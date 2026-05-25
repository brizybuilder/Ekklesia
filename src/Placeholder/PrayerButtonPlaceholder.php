<?php

namespace BrizyEkklesia\Placeholder;

use BrizyPlaceholders\ContentPlaceholder;
use BrizyPlaceholders\ContextInterface;

class PrayerButtonPlaceholder extends PlaceholderAbstract
{
    const NAME = 'ekk_prayer_button';

    public function echoValue(ContextInterface $context, ContentPlaceholder $placeholder)
    {
        echo '<div id="prayer-button-modal" class="brz-ministryBrands-PrayerButton-modal">
            <div class="brz-ministryBrands-PrayerButton-modal-dialog brz-ministryBrands-PrayerButton-modal-dialog-centered" role="document">
                <div class="brz-ministryBrands-PrayerButton-modal-content">';
        
        PrayingFormRenderer::renderFormContainer(
            'brz-ministryBrands-PrayerButton',
            'Submit a Prayer Request',
            true,
            [
                '<button type="button" class="brz-ministryBrands-PrayingForm-btn" data-dismiss="modal">Cancel</button>',
                '<button id="ekklesia360-prayer-submit" type="submit" name="submit" class="brz-ministryBrands-PrayingForm-btn">Submit</button>'
            ]
        );
        
        echo '</div>
            </div>

            <!-- Response Modal -->
            <div id="sfprayerresponse" class="brz-ministryBrands-PrayerButton-response-modal brz-ministryBrands-PrayerButton-modal" tabindex="-1"
                 aria-labelledby="responseModalLabel" aria-hidden="true">
                <div class="brz-ministryBrands-PrayerButton-modal-dialog brz-ministryBrands-PrayerButton-modal-dialog-centered">
                    <div class="brz-ministryBrands-PrayerButton-modal-content">';
        
        PrayingFormRenderer::renderResponseModalContent();
        
        echo '</div>
                </div>
            </div>
        </div>';
    }

}
