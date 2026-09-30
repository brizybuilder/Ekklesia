<?php

namespace BrizyEkklesia\Placeholder;

use BrizyPlaceholders\ContentPlaceholder;
use BrizyPlaceholders\ContextInterface;

class PrayerFormPlaceholder extends PlaceholderAbstract
{
    const NAME = 'ekk_prayer_form';

    public function echoValue(ContextInterface $context, ContentPlaceholder $placeholder)
    {
        PrayingFormRenderer::renderFormContainer(
            'brz-ministryBrands-PrayerForm',
            'Submit a Prayer Request',
            false,
            [
                '<button id="ekklesia360-prayer-submit" type="submit" name="submit" class="brz-ministryBrands-PrayingForm-btn">Submit</button>'
            ]
        );

        echo '<!-- Response Section -->
        <div id="sfprayerresponse" class="brz-ministryBrands-PrayerForm-response" style="display: none;">';
        
        PrayingFormRenderer::renderResponseModalContent();
        
        echo '</div>';
    }

}
