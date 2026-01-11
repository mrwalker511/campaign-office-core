/**
 * Campaign Office Core Frontend JS
 */

jQuery(document).ready(function ($) {

    /* =========================================
       Volunteer Form Handling
       ========================================= */
    $('.cp-volunteer-signup-form').on('submit', function (e) {
        e.preventDefault();

        var $form = $(this);
        var $message = $form.find('.cp-form-message');
        var $submitBtn = $form.find('.cp-volunteer-submit-btn');

        // Disable submit button
        $submitBtn.prop('disabled', true);

        // Collect form data
        var formData = new FormData($form[0]);
        formData.append('action', 'cp_submit_volunteer_signup');
        // Handle opportunity ID if present
        if ($form.data('opportunity-id')) {
            formData.append('opportunity_id', $form.data('opportunity-id'));
        }

        $.ajax({
            url: campaignOfficeCore.ajaxUrl,
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function (response) {
                if (response.success) {
                    $message.html('<div class="cp-success-message">' + response.data.message + '</div>');
                    $form[0].reset();
                } else {
                    $message.html('<div class="cp-error-message">' + (response.data.message || 'Error occurred') + '</div>');
                }
            },
            error: function () {
                $message.html('<div class="cp-error-message">Connection error. Please try again.</div>');
            },
            complete: function () {
                $submitBtn.prop('disabled', false);
            }
        });
    });

    /* =========================================
       Event RSVP Handling
       ========================================= */
    $('.cp-event-rsvp-form').on('submit', function (e) {
        e.preventDefault();

        var $form = $(this);
        var $message = $form.find('.cp-form-message');
        var $submitBtn = $form.find('.cp-rsvp-submit-btn');

        $submitBtn.prop('disabled', true);

        var formData = new FormData($form[0]);
        formData.append('action', 'cp_submit_event_rsvp');
        if ($form.data('event-id')) {
            formData.append('event_id', $form.data('event-id'));
        }

        $.ajax({
            url: campaignOfficeCore.ajaxUrl,
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function (response) {
                if (response.success) {
                    $message.html('<div class="cp-success-message">' + response.data.message + '</div>');
                    $form[0].reset();
                } else {
                    $message.html('<div class="cp-error-message">' + (response.data.message || 'Error occurred') + '</div>');
                }
            },
            error: function () {
                $message.html('<div class="cp-error-message">Connection error. Please try again.</div>');
            },
            complete: function () {
                $submitBtn.prop('disabled', false);
            }
        });
    });

    /* =========================================
       Calendar Handling
       ========================================= */
    $('.cp-event-calendar').each(function () {
        var $calendar = $(this);
        var $body = $calendar.find('.cp-calendar-body');
        var $title = $calendar.find('.cp-calendar-title');
        var $prevBtn = $calendar.find('.cp-calendar-prev');
        var $nextBtn = $calendar.find('.cp-calendar-next');
        var $viewBtns = $calendar.find('.cp-view-btn');

        var currentDate = new Date(); // Start with today
        var currentView = $calendar.data('view') || 'month';

        // Initialize
        updateCalendar();

        // View switching
        $viewBtns.on('click', function () {
            var view = $(this).data('view');
            if (view !== currentView) {
                currentView = view;
                $viewBtns.removeClass('active');
                $(this).addClass('active');
                updateCalendar();
            }
        });

        // Navigation
        $prevBtn.on('click', function () {
            changeDate(-1);
        });

        $nextBtn.on('click', function () {
            changeDate(1);
        });

        function changeDate(delta) {
            if (currentView === 'month') {
                currentDate.setMonth(currentDate.getMonth() + delta);
            } else if (currentView === 'week') {
                currentDate.setDate(currentDate.getDate() + (delta * 7));
            } else {
                // List view - typically 30 days
                currentDate.setDate(currentDate.getDate() + (delta * 30));
            }
            updateCalendar();
        }

        function formatDate(date) {
            var year = date.getFullYear();
            var month = ('0' + (date.getMonth() + 1)).slice(-2);
            return year + '-' + month; // Basic YYYY-MM for month view
        }

        function updateTitle() {
            var options = { year: 'numeric', month: 'long' };
            if (currentView === 'week') {
                // Determine week range title logic if needed, simplify to Month Year for now
            }
            $title.text(currentDate.toLocaleDateString('default', options));
        }

        function updateCalendar() {
            // Show loading state
            $body.css('opacity', '0.5');
            updateTitle();

            // Format date string for API
            // For month view, we need YYYY-MM-01
            // For others, just YYYY-MM-DD
            var dateStr = currentDate.toISOString().slice(0, 10);

            $.ajax({
                url: campaignOfficeCore.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'cp_get_calendar_events',
                    nonce: campaignOfficeCore.calendarNonce,
                    view: currentView,
                    date: dateStr
                },
                success: function (response) {
                    if (response.success) {
                        $body.html(response.data.html);
                        // Update active state of buttons match current view
                        $viewBtns.removeClass('active');
                        $calendar.find('.cp-view-btn[data-view="' + currentView + '"]').addClass('active');
                    }
                },
                complete: function () {
                    $body.css('opacity', '1');
                }
            });
        }
    });

});
