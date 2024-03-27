define([
    'jquery',
    'Magento_Search/form-mini'
], function ($) {
    $.widget('klevu.quickSearch', $.mage.quickSearch, {
        /**
         * \Klevu\FrontendSearch\Plugin\RequireJs\Config\File\Collector\AggregatedPlugin
         *   will remove this override if Klevu quick search is disabled
         */

        /**
         * Stop Magento auto-suggest from firing
         *
         * @private
         */
        _init: function () {
            this.options.minSearchLength = 999;
        },

        /**
         * Executes when keys are pressed in the search input field. Performs specific actions
         * depending on which keys are pressed.
         *
         * Override removes "e.preventDefault();" when ENTER key is pressed,
         *  enables KMC redirects to work as expected.
         *
         * @private
         * @param {Event} e - The key down event
         * @return {Boolean} Default return type for any unhandled keys
         */
        _onKeyDown: function (e) {
            var keyCode = e.keyCode || e.which;
            if ($.ui.keyCode.ENTER === keyCode) {
                if (this.element.val().length >= 1) {
                    this.searchForm.trigger('submit');
                }
                return true;
            }

            return this._super(e);
        },

        /**
         * Executes when the value of the search input field changes. Executes a GET request
         * to populate a suggestion list based on entered text. Handles click (select), hover,
         * and mouseout events on the populated suggestion list dropdown.
         *
         * Override always disabled the submit button after field changes
         *
         * @private
         */
        _onPropertyChange: function () {
            this._super();

            if (this.element.val().length >= 1) {
                this.submitBtn.disabled = false;
            }
        }
    });

    return $.klevu.quickSearch;
});
