/**
 * @package     J2Commerce
 * @subpackage  com_j2commerce
 *
 * @copyright   (C)2024-2026 J2Commerce, LLC <https://www.j2commerce.com>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
'use strict';

(function() {
    const config = Joomla.getOptions('com_j2commerce.builder') || {};
    const token = config.token || '';
    const ajaxUrl = config.ajaxUrl || 'index.php?option=com_j2commerce&task=builder.';

    // DOM references
    const fileSelect = document.getElementById('builder-file-select');
    const productSelect = document.getElementById('builder-product-select');
    const saveBtn = document.getElementById('builder-save');
    const undoBtn = document.getElementById('builder-undo');
    const redoBtn = document.getElementById('builder-redo');
    const canvas = document.getElementById('builder-canvas');
    const placeholder = document.getElementById('builder-placeholder');
    const blocksPanel = document.getElementById('builder-blocks-panel');
    const propertiesPanel = document.getElementById('builder-properties-panel');

    let editor = null;
    let isDirty = false;
    let isLoading = false;
    let currentBlockOrder = [];

    // ===== AJAX Helper =====
    async function fetchJson(task, params = {}, method = 'GET') {
        const url = new URL(ajaxUrl + task);
        url.searchParams.set(token, '1');

        const options = { method, headers: {} };

        if (method === 'GET') {
            Object.entries(params).forEach(([k, v]) => url.searchParams.set(k, v));
        } else {
            options.headers['Content-Type'] = 'application/json';
            options.body = JSON.stringify(params);
        }

        const response = await fetch(url.toString(), options);

        if (!response.ok) {
            throw new Error('Server error: ' + response.status);
        }

        const json = await response.json();

        if (!json.success) {
            throw new Error(json.message || 'Request failed');
        }

        return json.data;
    }

    // ===== Load Project =====
    async function loadProject(pluginElement, fileId) {
        try {
            const data = await fetchJson('loadProject', {
                plugin_element: pluginElement,
                file_id: fileId
            });

            // Guard: if the server classified this as a dispatcher, show info and bail
            if (data.file_type === 'dispatcher') {
                if (canvas) {
                    canvas.innerHTML = '<div class="alert alert-info m-3"><i class="fa-solid fa-code-branch me-2"></i>' +
                        (data.message || 'This file is a dispatcher and cannot be edited visually.') + '</div>';
                }
                saveBtn.disabled = true;
                return;
            }

            currentBlockOrder = data.block_order || [];
            populateBlockPalette(data.available_blocks || {});

            // Load the override file content into the canvas using file block order
            await renderFreshCanvas(pluginElement, fileId, data.available_blocks || {});

            saveBtn.disabled = false;
            if (placeholder) placeholder.style.display = 'none';
        } catch (err) {
            Joomla.renderMessages({ error: [err.message] });
        }
    }

    // ===== Render Fresh Canvas =====
    async function renderFreshCanvas(pluginElement, fileId, availableBlocks) {
        const productId = productSelect ? parseInt(productSelect.value, 10) : 0;

        // Use the file's block order if available, otherwise fall back to all blocks
        const slugOrder = currentBlockOrder.length > 0
            ? currentBlockOrder
            : Object.values(availableBlocks).map(b => b.slug);

        const blocks = slugOrder
            .filter(slug => availableBlocks[slug])
            .map(slug => ({
                slug: slug,
                settings: Object.fromEntries(
                    Object.entries(availableBlocks[slug].settings || {}).map(([k, v]) => [k, v.default])
                )
            }));

        try {
            const data = await fetchJson('renderAllBlocks', {
                product_id: productId,
                edit_mode: true,
                blocks: blocks
            }, 'POST');

            const blockHtml = Object.values(data.blocks || {}).join('\n');
            const html = '<div class="j2commerce-product-item j2commerce-type-simple d-flex flex-column" style="max-width:400px;">' + blockHtml + '</div>';
            initGrapesJS(null, html);
        } catch (err) {
            if (canvas) {
                canvas.innerHTML = '<div class="alert alert-danger m-3">' + err.message + '</div>';
            }
        }
    }

    // ===== Initialize GrapesJS =====
    function initGrapesJS(projectData, initialHtml) {
        if (editor) {
            editor.destroy();
        }

        if (canvas) canvas.innerHTML = '';

        editor = grapesjs.init({
            container: '#builder-canvas',
            fromElement: false,
            height: '500px',
            width: 'auto',
            storageManager: false,
            panels: { defaults: [] },
            blockManager: { blocks: [] },
            canvas: {
                styles: (config.canvasStyles || []).length
                    ? config.canvasStyles
                    : [
                        'https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css',
                        Joomla.getOptions('system.paths').root + '/media/com_j2commerce/css/site/j2commerce.css'
                    ]
            },
            plugins: [],
            pluginsOpts: {}
        });

        isLoading = true;
        if (projectData) {
            editor.loadProjectData(projectData);
        } else if (initialHtml) {
            editor.setComponents(initialHtml);
        }
        // Allow GrapesJS to settle, then clear undo history and enable dirty tracking
        requestAnimationFrame(() => {
            editor.UndoManager.clear();
            isDirty = false;
            isLoading = false;
        });

        // Make j2c-token elements non-editable but draggable
        editor.on('component:add', component => {
            if (component.get('tagName') === 'j2c-token') {
                component.set({
                    editable: false,
                    draggable: true,
                    removable: false,
                    copyable: false,
                    badgable: true,
                    highlightable: true
                });
                const tokenName = component.getAttributes()['data-j2c-token'] || 'TOKEN';
                component.set('custom-name', tokenName);
            }

            // cart-form block: draggable as a unit, nothing can be dropped inside
            const attrs = component.getAttributes();
            if (attrs['data-j2c-block'] === 'cart-form') {
                component.set({
                    droppable: false,
                    editable: false,
                    copyable: false,
                    removable: true,
                    draggable: true,
                    badgable: true,
                    highlightable: true
                });
                component.set('custom-name', 'Options & Cart');
                // Lock all children so nothing inside can be individually dragged out
                component.components().forEach(function lockChildren(child) {
                    child.set({ draggable: false, droppable: false, removable: false, copyable: false });
                    child.components().forEach(lockChildren);
                });
            }
        });

        editor.on('change:changesCount', () => {
            if (isLoading) return;
            isDirty = true;
            if (undoBtn) undoBtn.disabled = !editor.UndoManager.hasUndo();
            if (redoBtn) redoBtn.disabled = !editor.UndoManager.hasRedo();
        });

        editor.on('component:selected', component => {
            showProperties(component);
        });

        editor.on('component:deselected', () => {
            if (propertiesPanel) {
                propertiesPanel.innerHTML = '<div class="text-muted small text-center py-3">Select an element</div>';
            }
        });
    }

    // ===== Block Palette =====
    // Blocks replaced by the combined cart-form block
    const hiddenBlocks = ['product-options', 'product-cart'];

    function populateBlockPalette(blocks) {
        if (!blocksPanel) return;
        blocksPanel.innerHTML = '';

        Object.values(blocks).filter(block => !hiddenBlocks.includes(block.slug)).forEach(block => {
            const el = document.createElement('div');
            el.className = 'builder-block-item p-2 border rounded mb-2 cursor-pointer';
            el.draggable = true;
            el.dataset.slug = block.slug;
            el.innerHTML = '<i class="' + (block.icon || 'fa-solid fa-cube') + ' me-2"></i>' + (block.label || block.slug);

            el.addEventListener('dragstart', (e) => {
                e.dataTransfer.setData('text/plain', block.slug);
            });

            el.addEventListener('click', async () => {
                if (!editor) return;
                const productId = productSelect ? parseInt(productSelect.value, 10) : 0;
                const defaults = Object.fromEntries(
                    Object.entries(block.settings || {}).map(([k, v]) => [k, v.default])
                );

                try {
                    const data = await fetchJson('renderBlock', {
                        slug: block.slug,
                        product_id: productId,
                        edit_mode: true,
                        settings: JSON.stringify(defaults)
                    });
                    if (data.html) {
                        editor.addComponents(data.html);
                    }
                } catch (err) {
                    Joomla.renderMessages({ error: [err.message] });
                }
            });

            blocksPanel.appendChild(el);
        });
    }

    // ===== Properties Panel =====
    function createControlGroup(id, label, controlHtml, descHtml) {
        const group = document.createElement('div');
        group.className = 'control-group';
        group.innerHTML =
            '<div class="control-label">' +
                '<label id="' + id + '-lbl" for="' + id + '">' + label + '</label>' +
            '</div>' +
            '<div class="controls">' +
                controlHtml +
                (descHtml ? '<div id="' + id + '-desc"><small class="form-text">' + descHtml + '</small></div>' : '') +
            '</div>';
        return group;
    }

    function showProperties(component) {
        if (!propertiesPanel) return;
        propertiesPanel.innerHTML = '';

        const currentTag = component.get('tagName') || 'div';
        const tagOptions = ['div', 'span', 'h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'p', 'section', 'article']
            .map(t => '<option value="' + t + '"' + (currentTag === t ? ' selected' : '') + '>' + t + '</option>')
            .join('');
        const tagGroup = createControlGroup(
            'prop-tag', 'HTML Tag',
            '<select id="prop-tag" class="form-select" aria-describedby="prop-tag-desc">' + tagOptions + '</select>',
            'The HTML element type for this component.'
        );
        propertiesPanel.appendChild(tagGroup);
        tagGroup.querySelector('select').addEventListener('change', (e) => {
            component.set('tagName', e.target.value);
        });

        const currentClasses = component.getClasses().join(' ');
        const classGroup = createControlGroup(
            'prop-classes', 'CSS Classes',
            '<input type="text" id="prop-classes" class="form-control" value="' + currentClasses + '" aria-describedby="prop-classes-desc">',
            'Space-separated CSS class names.'
        );
        propertiesPanel.appendChild(classGroup);
        classGroup.querySelector('input').addEventListener('change', (e) => {
            component.setClass(e.target.value.split(' ').filter(Boolean));
        });

        const isVisible = component.getStyle().display !== 'none';
        const visGroup = createControlGroup(
            'prop-visible', 'Visible',
            '<fieldset id="prop-visible" class="radio switcher">' +
                '<input type="radio" id="prop-visible-0" name="prop-visible" value="0"' + (!isVisible ? ' checked' : '') + '>' +
                '<label for="prop-visible-0">No</label>' +
                '<input type="radio" id="prop-visible-1" name="prop-visible" value="1"' + (isVisible ? ' checked' : '') + '>' +
                '<label for="prop-visible-1">Yes</label>' +
            '</fieldset>',
            'Toggle element visibility.'
        );
        propertiesPanel.appendChild(visGroup);
        visGroup.querySelectorAll('input[name="prop-visible"]').forEach(radio => {
            radio.addEventListener('change', (e) => {
                component.setStyle({ display: e.target.value === '1' ? '' : 'none' });
            });
        });
    }

    // ===== Extract Block Order from GrapesJS Canvas =====
    function extractBlockOrder() {
        if (!editor) return [];

        const order = [];
        const wrapper = editor.getWrapper();

        function walk(component) {
            const attrs = component.getAttributes();
            const blockSlug = attrs['data-j2c-block'];
            if (blockSlug && !order.includes(blockSlug)) {
                order.push(blockSlug);
            }
            component.components().forEach(child => walk(child));
        }

        walk(wrapper);
        return order;
    }

    // ===== Save =====
    async function saveLayout() {
        if (!editor || !fileSelect || !fileSelect.value) return;

        const [pluginElement, fileId] = fileSelect.value.split('::');
        const blockOrder = extractBlockOrder();

        console.log('[J2C Builder] Extracted block order:', blockOrder);

        if (blockOrder.length === 0) {
            Joomla.renderMessages({ warning: ['No blocks found on canvas. Nothing to save.'] });
            return;
        }

        saveBtn.disabled = true;
        saveBtn.innerHTML = '<i class="fa-solid fa-spinner fa-spin me-1"></i>Saving...';

        try {
            const saveResult = await fetchJson('saveSubLayout', {
                plugin_element: pluginElement,
                file_id: fileId,
                block_order: blockOrder
            }, 'POST');

            console.log('[J2C Builder] Save result:', saveResult);
            isDirty = false;

            // Redirect to editor tab so the code editor shows the freshly saved content
            const editorFile = btoa('layouts::' + fileId);
            window.location.href = 'index.php?option=com_j2commerce&view=overrides&tab=editor'
                + '&plugin=' + encodeURIComponent(pluginElement)
                + '&file=' + encodeURIComponent(editorFile);
        } catch (err) {
            Joomla.renderMessages({ error: [err.message] });
            saveBtn.disabled = false;
            saveBtn.innerHTML = '<i class="fa-solid fa-floppy-disk me-1"></i>Save Layout';
        }
    }

    // ===== Device Switching =====
    document.querySelectorAll('[data-device]').forEach(btn => {
        btn.addEventListener('click', () => {
            if (!editor) return;

            document.querySelectorAll('[data-device]').forEach(b => b.classList.remove('active'));
            btn.classList.add('active');

            editor.setDevice(btn.dataset.device);

            const widths = { desktop: '100%', tablet: '768px', mobile: '375px' };
            const frame = canvas ? canvas.querySelector('iframe') : null;
            if (frame) {
                frame.style.width = widths[btn.dataset.device] || '100%';
            }
        });
    });

    // ===== Event Listeners =====
    if (fileSelect) {
        fileSelect.addEventListener('change', () => {
            if (!fileSelect.value) return;
            const [pluginElement, fileId] = fileSelect.value.split('::');
            loadProject(pluginElement, fileId);
        });
    }

    if (productSelect) {
        productSelect.addEventListener('change', async () => {
            if (!editor || !fileSelect || !fileSelect.value) return;
            const [pluginElement, fileId] = fileSelect.value.split('::');
            await loadProject(pluginElement, fileId);
        });
    }

    if (saveBtn) {
        saveBtn.addEventListener('click', saveLayout);
    }

    if (undoBtn) {
        undoBtn.addEventListener('click', () => editor && editor.UndoManager.undo());
    }

    if (redoBtn) {
        redoBtn.addEventListener('click', () => editor && editor.UndoManager.redo());
    }

    window.addEventListener('beforeunload', (e) => {
        if (isDirty) {
            e.preventDefault();
            e.returnValue = '';
        }
    });

    // ===== Populate File Selector =====
    function populateFileSelector() {
        if (!fileSelect) return;

        const subLayouts = config.subLayoutFiles || [];

        subLayouts.forEach(sl => {
            const opt = document.createElement('option');
            opt.value = sl.value;
            opt.textContent = sl.label;
            fileSelect.appendChild(opt);
        });
    }

    populateFileSelector();
})();
