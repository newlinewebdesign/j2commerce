<?php
/**
 * @package     J2Commerce
 * @subpackage  com_j2commerce
 *
 * @copyright   (C)2024-2026 J2Commerce, LLC <https://www.j2commerce.com>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */


// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

use J2Commerce\Component\J2commerce\Administrator\Helper\J2CommerceHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Form\Field\RadioField;
use Joomla\CMS\Form\Form;
use Joomla\CMS\HTML\Helpers\User;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Layout\FileLayout;
use Joomla\CMS\Layout\LayoutHelper;
use Joomla\CMS\Uri\Uri;

$image_counter = 0;

$this->item = $displayData['product'];
$this->form_prefix = $displayData['form_prefix'] ?? 'jform[attribs][j2commerce]';
?>

<div class="j2commerce-additional-product-images">
    <fieldset class="options-form">
        <legend><?php echo Text::_('COM_J2COMMERCE_PRODUCT_ADDITIONAL_IMAGES') ?></legend>
        <table id="additionalImages" class="table align-middle">
            <thead>
            <tr>
                <td colspan="3">
                    <div class="pull-right">
                        <input type="button" id="addImagBtn" class="btn btn-success"
                               value="<?php echo Text::_('COM_J2COMMERCE_PRODUCT_ADDITIONAL_IMAGES_ADD') ?>"/>
                    </div>
                </td>
            </tr>
            </thead>
            <tr>
                <th>
                    <label
                    <?php //echo J2Html::label(Text::_('J2STORE_PRODUCT_ADDITIONAL_IMAGE'), 'additioanl_image_label'); ?>
                </th>
                <th>
                    <?php //echo J2Html::label(Text::_('J2STORE_PRODUCT_ADDITIONAL_IMAGE_ALT_TEXT'), 'additioanl_image_label'); ?>
                </th>
                <th class="text-end">
                    <?php echo Text::_('COM_J2COMMERCE_DELETE'); ?>
                </th>
            </tr>
            <?php
            if (isset($this->item->additional_images) && !empty($this->item->additional_images)):?>
                <?php
                $add_image = json_decode($this->item->additional_images);
                $add_image_alt = json_decode($this->item->additional_images_alt,true);
                ?>
            <?php endif;
            if (isset($add_image) && !empty($add_image)):
                foreach ($add_image as $key => $img):?>
                    <tbody class="tr-additional-image" id="additional-image-<?php echo $key; ?>">
                    <tr>
                        <td colspan="1">
                            <?php echo LayoutHelper::render(
                                'joomla.form.field.media',
                                [
                                    'name'             => $this->form_prefix . '[additional_images][' . $key . ']',
                                    'id'               => 'additional_image_' . $key,
                                    'value'            => $img ?? '',
                                    'class'            => 'form-control image-input',
                                    'image_id' => 'input-additional-image-' . $key,
                                    'mediaTypeNames'   => ['images'],
                                    'no_hide' => '',
                                    'mediaTypes'       => '0',
                                    'preview'          => 'show',
                                    'previewWidth'     => 100,
                                    'previewHeight'    => 100,
                                    'imagesAllowedExt' => ['jpg','jpeg','png','gif','webp','svg'],
                                    'imagesExt'        => ['jpg','jpeg','png','gif','webp','svg'],
                                ]
                            );
                            ?>
                            <?php //echo J2Html::media($this->form_prefix . '[additional_images][' . $key . ']', $img, array('id' => 'additional_image_' . $key, 'class' => 'image-input', 'image_id' => 'input-additional-image-' . $key, 'no_hide' => '')); ?>
                        </td>
                        <td>
                            <?php echo LayoutHelper::render('joomla.form.field.text', ['name'  => $this->form_prefix.'[additional_images_alt][' . $key . ']','id'    => 'additional_image_alt_' . $key,'value' => isset($add_image_alt[$key])?$add_image_alt[$key]:'','class' => 'form-control',]);?>
                            <?php //echo J2Html::text($this->form_prefix . '[additional_images_alt][' . $key . ']', isset($add_image_alt[$key])?$add_image_alt[$key]:'', array('id' => 'additional_image_alt_' . $key)); ?>
                        </td>
                        <td class="text-end">
                            <button type="button" onclick="deleteImageRow(this)" class="btn btn-link btn-sm text-danger" title="<?php echo Text::_('COM_J2COMMERCE_DELETE') ?>">
                                <span class="icon icon-trash text-danger"></span>
                            </button>
                        </td>
                    </tr>
                    </tbody>
                    <?php
                    if ($key >= $image_counter)
                    {
                        $image_counter = $key;
                    }
                    $image_counter++;
                    ?>
                <?php endforeach; ?>
            <?php else: ?>
                <tbody class="tr-additional-image" id="additional-image-0">
                <tr>
                    <td colspan="1">
                        <?php echo LayoutHelper::render(
                            'joomla.form.field.media',
                            [
                                'name'             => $this->form_prefix . '[additional_images][0]',
                                'id'               => 'additional_image_0',
                                'value'            => $this->item->main_image ?? '',
                                'class'            => 'form-control image-input',
                                'mediaTypeNames'   => ['images'],
                                'mediaTypes'       => '0',
                                'preview'          => 'show',
                                'previewWidth'     => 100,
                                'previewHeight'    => 100,
                                'imagesAllowedExt' => ['jpg','jpeg','png','gif','webp','svg'],
                                'imagesExt'        => ['jpg','jpeg','png','gif','webp','svg'],
                            ]
                        );
                        ?>
                    </td>
                    <td>
                        <?php echo LayoutHelper::render('joomla.form.field.text', ['name'  => $this->form_prefix.'[additional_images_alt][0]','id'    => 'additional_image_alt_0','value' => '','class' => 'form-control',]);?>
                    </td>
                    <td><input type="button" onclick="deleteImageRow(this)" class="btn btn-success" value="<?php echo Text::_('COM_J2COMMERCE_DELETE') ?>"/></td>
                </tr>
                </tbody>
            <?php endif; ?>
            <!-- DO NOT DELETE - START - HTML needed for the add an additional image script -->
            <input type="hidden" id="additional_image_counter" name="additional_image_counter" value="<?php echo $image_counter; ?>"/>
            <tbody class="tr-additional-image" id="additional-image-template" style="display: none;">
            <tr>
                <td colspan="1">
                    <?php echo LayoutHelper::render(
                        'joomla.form.field.media',
                        [
                            'name'             => 'additional_image_tmpl',
                            'id'               => 'additional_image_',
                            'value'            => '',
                            'class'            => 'form-control image-input',
                            'image_id' => 'input-additional-image-',
                            'mediaTypeNames'   => ['images'],
                            'mediaTypes'       => '0',
                            'preview'          => 'show',
                            'previewWidth'     => 100,
                            'previewHeight'    => 100,
                            'imagesAllowedExt' => ['jpg','jpeg','png','gif','webp','svg'],
                            'imagesExt'        => ['jpg','jpeg','png','gif','webp','svg'],
                        ]
                    );
                    ?>
                </td>
                <td>
                    <?php //echo J2Html::text('additional_images_alt_tmpl', '', array('id' => 'additional_image_alt_', 'class' => 'image-alt-text')); ?>
                </td>
                <td>
                    <input type="button" onclick="deleteImageRow(this)" class="btn btn-success" value="<?php echo Text::_('COM_J2COMMERCE_DELETE') ?>"/>
                </td>

            </tr>
            </tbody>
            <!-- DO NOT DELETE - END - HTML needed for the additional image script -->
        </table>
        <div class="alert alert-info">
            <h4><?php echo Text::_('COM_J2COMMERCE_QUICK_HELP'); ?></h4>
            <h5><?php echo Text::_('COM_J2COMMERCE_FEATURE_AVAILABLE_IN_J2STORE_PRODUCT_LAYOUTS_AND_ARTICLES'); ?></h5>
            <p><?php echo Text::_('COM_J2COMMERCE_PRODUCT_IMAGES_HELP_TEXT'); ?></p>
        </div>
    </fieldset>
    <?php echo J2CommerceHelper::plugin()->eventWithHtml('AfterAdditionalProductImagesForm', array($this, $this->item, $this->form_prefix))->getArgument('html', ''); ?>
</div>
<div class="row-fluid">
    <div class="span12">
        <div class="control-group">
            <?php //echo J2Html::label(Text::_('J2STORE_PRODUCT_THUMB_IMAGE'), 'thumb_image', array('control-label')); ?>
            <?php //echo J2Html::media($this->form_prefix . '[thumb_image]', $this->item->thumb_image, array('id' => 'thumb_image', 'image_id' => 'input-thumb-image', 'no_hide' => '')); ?>
        </div>
        <div class="control-group">
            <?php //echo J2Html::label(Text::_('J2STORE_PRODUCT_THUMB_IMAGE_ALT_TEXT'), 'thumb_image_alt', array('control-label')); ?>
            <?php //echo J2Html::text($this->form_prefix . '[thumb_image_alt]', $this->item->thumb_image_alt, array('id' => 'thumb_image_alt')); ?>
        </div>
        <div class="control-group">
            <?php //echo J2Html::label(Text::_('J2STORE_PRODUCT_MAIN_IMAGE'), 'main_image', array('control-label')); ?>
            <?php //echo J2Html::media($this->form_prefix . '[main_image]', $this->item->main_image, array('id' => 'main_image', 'image_id' => 'input-main-image', 'no_hide' => '')); ?>
            <?php //echo J2Html::hidden($this->form_prefix . '[j2commerce_productimage_id]', $this->item->j2commerce_productimage_id); ?>
        </div>
        <div class="control-group">
            <?php //echo J2Html::label(Text::_('J2STORE_PRODUCT_MAIN_IMAGE_ALT_TEXT'), 'main_image_alt', array('control-label')); ?>
            <?php //echo J2Html::text($this->form_prefix . '[main_image_alt]', $this->item->main_image_alt, array('id' => 'main_image_alt')); ?>
        </div>


    </div>
</div>

<script type="text/javascript">

    function deleteImageRow(element) {
        const tbody = element.closest('.tr-additional-image');

        if (document.querySelectorAll(".tr-additional-image").length === 2) {
            // reset the last item
            const image_div = document.getElementById("additional-image-template");
            addAdditionalImage(image_div, 0, '<?php echo $joomla_version;?>');
            document.getElementById("additional-image-0").classList.add('hide');
        }
        tbody.remove();
    }

    let additional_image_counter = <?php echo $image_counter;?>;

    document.getElementById("addImagBtn").addEventListener('click', function() {
        additional_image_counter = parseInt(document.getElementById("additional_image_counter").value);
        additional_image_counter++;
        const image_div = document.getElementById("additional-image-template");
        addAdditionalImage(image_div, additional_image_counter, '<?php echo $joomla_version;?>');
        document.getElementById("additional_image_counter").value = additional_image_counter;
    });

    function addAdditionalImage(image_div, additional_image_counter, joomla_version) {
        // Clone the template
        const clone = image_div.cloneNode(true);
        clone.id = 'additional-image-' + additional_image_counter;

        // Update image preview elements
        clone.querySelectorAll('.j2commerce-media-slider-image-preview').forEach(function(img) {
            img.src = '<?php echo Uri::root() . 'media/j2commerce/images/common/no_image-100x100.jpg'; ?>';
            const inputElement = document.getElementById('input-additional-image-' + additional_image_counter);
            if (!inputElement || inputElement.innerHTML === '') {
                img.id = 'input-additional-image-' + additional_image_counter;
            }
        });

        // Update text input elements
        clone.querySelectorAll('input[type="text"]').forEach(function(input) {
            const is_alt_text = input.classList.contains('image-alt-text');
            const input_name = is_alt_text ? 'additional_images_alt' : 'additional_images';
            input.name = "<?php echo $this->form_prefix ?>[" + input_name + "][" + additional_image_counter + "]";
            input.value = '';
            input.id = 'jform_image_additional_image_' + additional_image_counter;
            input.setAttribute('image_id', 'input-additional-image-' + additional_image_counter);
            if (joomla_version == 1 || joomla_version == 4) {
                input.setAttribute('onchange', 'previewImage(this,jform_image_additional_image_' + additional_image_counter + ')');
            }
        });

        clone.classList.remove('hide');

        // Handle Joomla 3.5 modal links
        if (joomla_version == 0) {
            clone.querySelectorAll('.modal').forEach(function(modal) {
                modal.href = 'index.php?option=com_media&view=images&tmpl=component&asset=1&author=673&fieldid=jform_image_additional_image_' + counter + '&folder=';
            });
        } else if (joomla_version == 1) {
            // For Joomla 3.5 - add media field script
            const script = document.createElement('script');
            script.src = '<?php echo Uri::root(true) . '/media/media/js/mediafield.min.js'?>';
            script.type = 'text/javascript';
            clone.appendChild(script);
        }

        // Insert the cloned element after the last tbody
        const lastTbody = document.querySelector('#additionalImages tbody:last-of-type');
        if (lastTbody) {
            lastTbody.parentNode.insertBefore(clone, lastTbody.nextSibling);
        }

        clone.style.display = '';
    }
</script>

<!--<script type="text/javascript">

    function deleteImageRow(element) {
        (function ($) {
            var tbody = $(element).closest('.tr-additional-image');

            if ($(".tr-additional-image").length == 2) {
                // reset the last item
                var image_div = jQuery("#additional-image-template");
                addAdditionalImage(image_div, 0, '<?php /*echo $joomla_version;*/?>');
                jQuery("#additional-image-0").addClass('hide');
            }
            tbody.remove();
        })(j2commerce.jQuery);
    }

    var counter = <?php /*echo $image_counter;*/?>;

    jQuery("#addImagBtn").click(function () {
        counter = jQuery("#additional_image_counter").val();
        counter++;
        (function ($) {
            var image_div = jQuery("#additional-image-template");
            addAdditionalImage(image_div, counter, '<?php /*echo $joomla_version;*/?>');
        })(j2commerce.jQuery);
        jQuery("#additional_image_counter").val(counter);
    })

    function addAdditionalImage(image_div, counter, joomla_version) {
        (function ($) {
            //increment the
            var clone = image_div.clone();
            clone.attr('id', 'additional-image-' + counter);
            //need to change the input name
            clone.find('.j2commerce-media-slider-image-preview').each(function () {
                $(this).attr('src', '<?php /*echo JUri::root() . 'media/j2commerce/images/common/no_image-100x100.jpg'; */?>');
                if ($('#input-additional-image-' + counter).html() == '') {
                    $(this).attr("id", 'input-additional-image-' + counter);
                }
            });
            clone.find(':text').each(function () {
                var is_alt_text = $(this).hasClass('image-alt-text');
                var input_name = (is_alt_text) ? 'additional_images_alt' : 'additional_images';
                $(this).attr("name", "<?php /*echo $this->form_prefix */?>[" + input_name + "][" + counter + "]");
                $(this).attr("value", '');
                $(this).attr("id", 'jform_image_additional_image_' + counter);
                $(this).attr("image_id", 'input-additional-image-' + counter);
                if (joomla_version == 1 || joomla_version == 4) {
                    $(this).attr("onchange", 'previewImage(this,jform_image_additional_image_' + counter + ')');
                }
            });
            clone.removeClass('hide');
            //remove joomla 3.5
            if (joomla_version == 0) {
                clone.find('.modal').each(function () {
                    $(this).attr('href', 'index.php?option=com_media&view=images&tmpl=component&asset=1&author=673&fieldid=jform_image_additional_image_' + counter + '&folder=');
                });
            } else if (joomla_version == 1) {
                //for joomla 3.5
                clone.append('<script src="<?php /*echo JUri::root(true) . '/media/media/js/mediafield.min.js'*/?>" type="text\/javascript"><\/script>');
            }
            //to chang label id
            var new_html = image_div.before(clone);
            //now it is placed just of the image div so remove the element
            var processed_html = clone.remove();
            //get the newly added tbody and insert after the additional-image-0
            $(processed_html).insertAfter($('#additionalImages tbody:last-child'));
            $(processed_html).show();
            // initialize squeeze box again for edit button to work
            // no need in joomla 3.5
            if (joomla_version == 0) {
                //window.parent.SqueezeBox.initialize({});
                //window.parent.SqueezeBox.assign($('a.modal'), {
                //	parse: 'rel'
                //});
                SqueezeBox.initialize({});
                SqueezeBox.assign($('#additional-image-' + counter + ' a.modal'), {
                    parse: 'rel'
                });
            }
        })(j2commerce.jQuery);
    }
</script>-->
