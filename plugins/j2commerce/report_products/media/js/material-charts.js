/**
 * Material Charts - Vanilla JavaScript Bar Chart Library
 *
 * @package  J2Commerce Report Products
 * @since    6.0.0
 *
 * Rewritten from jQuery to vanilla ES6+ for Joomla 6 compatibility.
 * Only bar chart functionality is included (pie chart removed — not used).
 */

'use strict';

const MaterialCharts = {
    /**
     * Render a vertical bar chart inside the target element.
     *
     * @param {string} selector  CSS selector for the container element
     * @param {object} data      Chart configuration
     * @param {object} data.datasets         { values: number[], labels: string[], color: string }
     * @param {string} [data.title]          Chart title
     * @param {string} data.height           Chart height (e.g. "300px")
     * @param {string} data.width            Chart width  (e.g. "500px")
     * @param {string} [data.background]     Background color
     * @param {string} [data.shadowDepth]    Shadow depth (1-5)
     * @param {boolean} [data.noY]           Hide Y-axis
     */
    bar(selector, data) {
        const container = document.querySelector(selector);
        if (!container) return;

        this._helpers.initializeChartArea(container, data.height, data.width, data.background, data.shadowDepth, 'bar');

        const validation = this._validators.validateBarChartData(data);
        if (!validation.valid) {
            this._helpers.insertErrorMessage(container, validation.message);
            return;
        }

        if (data.title) {
            this._helpers.insertTitle(container, data.title);
        }

        this._bar.insertAxes(container, data.noY);
        this._bar.insertData(
            container,
            parseInt(data.height, 10),
            parseInt(data.width, 10),
            data.datasets.values,
            Math.max(...data.datasets.values),
            data.datasets.labels,
            data.datasets.color,
            data.noY
        );

        // Tooltip follow cursor
        this._bar.attachTooltipBehavior(container, selector);
    },

    _helpers: {
        initializeChartArea(el, height, width, background, shadowDepth, type) {
            el.classList.add('material-charts-chart-area', 'material-charts-' + type);

            // Tooltip element
            const hover = document.createElement('div');
            hover.className = 'material-charts-hover';
            const hoverText = document.createElement('div');
            hoverText.className = 'material-charts-hover-text';
            hover.appendChild(hoverText);
            el.appendChild(hover);

            el.style.height = height;
            el.style.width = width;
            el.style.backgroundColor = background || 'transparent';

            if (shadowDepth) {
                el.classList.add('material-charts-shadow-' + shadowDepth);
            }
        },

        insertTitle(el, title) {
            const titleEl = document.createElement('div');
            titleEl.className = 'material-charts-chart-title';
            titleEl.textContent = title;
            el.appendChild(titleEl);
        },

        insertErrorMessage(el, message) {
            const errEl = document.createElement('div');
            errEl.className = 'material-charts-error-message';
            errEl.innerHTML = '<b>' + message + '</b>';
            el.appendChild(errEl);
        }
    },

    _bar: {
        insertAxes(el, noY) {
            const xAxis = document.createElement('div');
            xAxis.className = 'material-charts-box-chart-x-axis';
            el.appendChild(xAxis);

            if (!noY) {
                const yAxis = document.createElement('div');
                yAxis.className = 'material-charts-box-chart-y-axis';
                el.appendChild(yAxis);
            }
        },

        insertData(el, height, width, values, dataMax, labels, color, noY) {
            let tickMax = dataMax;
            while (tickMax % 5 !== 0) {
                tickMax++;
            }

            const absoluteHeightMultiplier = (height - 60) / tickMax;
            const verticalSpread = (tickMax / 5) * absoluteHeightMultiplier;
            const startTickPosition = 25;
            const endTickPosition = (25 + tickMax) * absoluteHeightMultiplier - 25 * absoluteHeightMultiplier;

            // Y-axis ticks
            if (!noY) {
                for (let i = startTickPosition + verticalSpread; i <= endTickPosition; i += verticalSpread) {
                    this._insertVerticalTick(el, i, (i - 25) / absoluteHeightMultiplier);
                }
            }

            // Bars and labels
            const horizontalSpread = (width - 50) / (labels.length + 1);
            let xPos = startTickPosition + horizontalSpread;

            for (let barIdx = 0; barIdx < labels.length; barIdx++) {
                this._insertHorizontalLabel(el, xPos, labels[barIdx]);
                this._insertVerticalBar(el, xPos, horizontalSpread, values[barIdx] * absoluteHeightMultiplier, values[barIdx], color);
                xPos += horizontalSpread;
            }
        },

        _insertVerticalTick(el, heightPos, label) {
            const tick = document.createElement('div');
            tick.className = 'material-charts-box-chart-vertical-tick';
            tick.style.bottom = heightPos + 'px';
            el.appendChild(tick);

            const tickLabel = document.createElement('div');
            tickLabel.className = 'material-charts-box-chart-vertical-tick-label';
            tickLabel.style.bottom = (heightPos - 4) + 'px';
            tickLabel.textContent = Math.round(label).toString();
            el.appendChild(tickLabel);
        },

        _insertHorizontalLabel(el, horizontalPos, label) {
            const labelEl = document.createElement('div');
            labelEl.className = 'material-charts-box-chart-horizontal-label';
            labelEl.style.left = horizontalPos + 'px';
            labelEl.textContent = label;
            labelEl.title = label;
            el.appendChild(labelEl);

            // Center the label under the bar after render
            requestAnimationFrame(() => {
                const width = labelEl.getBoundingClientRect().width;
                const oldLeft = parseFloat(labelEl.style.left);
                labelEl.style.left = (oldLeft - width / 2) + 'px';
            });
        },

        _insertVerticalBar(el, horizontalPos, horizontalSpread, height, value, color) {
            const bar = document.createElement('div');
            bar.className = 'material-charts-box-chart-vertical-bar material-charts-' + color;
            bar.style.left = (horizontalPos - horizontalSpread / 4) + 'px';
            bar.style.width = (horizontalSpread / 2) + 'px';
            bar.style.height = height + 'px';
            bar.dataset.hover = value;
            el.appendChild(bar);
        },

        attachTooltipBehavior(container, selector) {
            const hover = container.querySelector('.material-charts-hover');
            const hoverText = container.querySelector('.material-charts-hover-text');
            if (!hover || !hoverText) return;

            container.addEventListener('mouseenter', function (e) {
                const bar = e.target.closest('.material-charts-box-chart-vertical-bar');
                if (!bar) return;
                hoverText.textContent = bar.dataset.hover;
                hover.style.display = 'block';
            }, true);

            container.addEventListener('mouseleave', function (e) {
                const bar = e.target.closest('.material-charts-box-chart-vertical-bar');
                if (!bar) return;
                hover.style.display = 'none';
            }, true);

            container.addEventListener('mousemove', function (e) {
                if (hover.style.display !== 'none') {
                    hover.style.top = (e.clientY - 40) + 'px';
                    hover.style.left = (e.clientX + 15) + 'px';
                }
            });
        }
    },

    _validators: {
        validateBarChartData(data) {
            const result = { valid: true, message: '' };

            if (!data.datasets || !data.datasets.values || data.datasets.values.length === 0) {
                result.valid = false;
                result.message = 'Material Charts: No data values provided.';
                return result;
            }

            if (!data.datasets.labels || data.datasets.labels.length === 0) {
                result.valid = false;
                result.message = 'Material Charts: No data labels provided.';
                return result;
            }

            if (data.datasets.labels.length !== data.datasets.values.length) {
                result.valid = false;
                result.message = 'Material Charts: Labels and values must be the same length.';
                return result;
            }

            if (!data.datasets.color) {
                result.valid = false;
                result.message = 'Material Charts: Dataset color must be specified.';
                return result;
            }

            if (!data.height || !data.width) {
                result.valid = false;
                result.message = 'Material Charts: Chart must have a height and width.';
                return result;
            }

            return result;
        }
    }
};

// Export for global access
window.MaterialCharts = MaterialCharts;
