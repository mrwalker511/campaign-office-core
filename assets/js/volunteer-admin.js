/**
 * CampaignPress Core - Volunteer Admin Scripts
 *
 * @package Campaign_Office_Core
 * @since 1.0.0
 */

(function ($) {
    'use strict';

    /**
     * Volunteer Admin Handler
     */
    var CPVolunteerAdmin = {
        /**
         * Initialize
         */
        init: function () {
            this.bindEvents();
        },

        /**
         * Bind event handlers
         */
        bindEvents: function () {
            // Select all checkbox
            $('#cb-select-all').on('change', this.toggleSelectAll);

            // Status update quick action
            $('.cp-quick-status-change').on('click', this.handleQuickStatusChange);

            // Confirm delete
            $('.submitdelete').on('click', this.confirmDelete);

            // Notes save
            $('.cp-save-notes').on('click', this.saveNotes);
        },

        /**
         * Toggle select all checkboxes
         */
        toggleSelectAll: function () {
            var isChecked = $(this).prop('checked');
            $('input[name="volunteer_ids[]"]').prop('checked', isChecked);
        },

        /**
         * Handle quick status change
         */
        handleQuickStatusChange: function (e) {
            e.preventDefault();
            var $link = $(this);
            var volunteerId = $link.data('volunteer-id');
            var newStatus = $link.data('status');

            if (!confirm('Change status to ' + newStatus + '?')) {
                return;
            }

            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {
                    action: 'cp_update_volunteer_status',
                    volunteer_id: volunteerId,
                    status: newStatus,
                    nonce: cpVolunteerAdmin.nonce
                },
                success: function (response) {
                    if (response.success) {
                        location.reload();
                    } else {
                        alert(response.data.message || 'Failed to update status.');
                    }
                },
                error: function () {
                    alert('An error occurred. Please try again.');
                }
            });
        },

        /**
         * Confirm delete action
         */
        confirmDelete: function (e) {
            if (!confirm('Are you sure you want to delete this volunteer? This action cannot be undone.')) {
                e.preventDefault();
            }
        },

        /**
         * Save volunteer notes
         */
        saveNotes: function (e) {
            e.preventDefault();
            var $btn = $(this);
            var volunteerId = $btn.data('volunteer-id');
            var notes = $('#cp-volunteer-notes-' + volunteerId).val();

            $btn.prop('disabled', true).text('Saving...');

            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {
                    action: 'cp_save_volunteer_notes',
                    volunteer_id: volunteerId,
                    notes: notes,
                    nonce: cpVolunteerAdmin.nonce
                },
                success: function (response) {
                    if (response.success) {
                        $btn.text('Saved!');
                        setTimeout(function () {
                            $btn.prop('disabled', false).text('Save Notes');
                        }, 2000);
                    } else {
                        alert(response.data.message || 'Failed to save notes.');
                        $btn.prop('disabled', false).text('Save Notes');
                    }
                },
                error: function () {
                    alert('An error occurred. Please try again.');
                    $btn.prop('disabled', false).text('Save Notes');
                }
            });
        }
    };

    // Initialize on document ready
    $(document).ready(function () {
        CPVolunteerAdmin.init();
    });

})(jQuery);
