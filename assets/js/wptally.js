/*global jQuery, document, edd_free_downloads_vars, jBox, isMobile*/
/*jslint newcap: true*/
jQuery(document.body).ready(function ($) {
    'use strict';

    $('.tally-search-results-plugins-header').on('click', function (e) {
        e.preventDefault();

        if (! $(this).hasClass('active')) {
            $(this).addClass('active');
            $('.tally-search-results-themes-header').removeClass('active');

            $('.tally-search-results-themes').fadeOut('fast', function () {
                $(this).css('display', 'none');
            });
            $('.tally-search-results-plugins').fadeIn('fast').css('display', 'block');
        }
    });

	$('.tally-search-results-themes-header').on('click', function (e) {
        e.preventDefault();

        if (! $(this).hasClass('active')) {
            $(this).addClass('active');
            $('.tally-search-results-plugins-header').removeClass('active');

			$('.tally-search-results-plugins').fadeOut('fast', function () {
                $(this).css('display', 'none');
            });
            $('.tally-search-results-themes').fadeIn('fast').css('display', 'block');
        }
    });
});
