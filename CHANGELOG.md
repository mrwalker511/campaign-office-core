# Changelog - CampaignPress Core Plugin

All notable changes to the CampaignPress Core plugin will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

---

## [2.0.0] - 2026-01-10

### Changed
- **Version Alignment:** Updated version to 2.0.0 to align with CampaignPress theme version
- **Documentation:** Comprehensive code review and documentation updates
- **Database:** Optimized contact manager table with better indexing
- **Compatibility:** Verified WordPress 6.9 compatibility

### Added
- **Developer Hooks:** Added action hooks for theme integration
- **Templates:** Press release CPT now has corresponding theme templates
- **Documentation:** Created PLUGIN-REVIEW.md with comprehensive analysis
- **Documentation:** Added CHANGELOG.md for version tracking

### Improved
- **Contact Manager:** Enhanced deduplication logic
- **Event Calendar:** Better iCal export formatting
- **Code Quality:** Updated code comments and PHPDoc blocks
- **Security:** Reinforced input sanitization and nonce verification

### Fixed
- **Database Creation:** Improved timing for table creation hooks
- **Version Checking:** Better database schema version management
- **REST API:** Ensured all CPTs have proper REST API support

### Tested
- ✅ WordPress 6.9 compatibility verified
- ✅ PHP 7.4 - 8.2 compatibility confirmed
- ✅ All custom post types functioning correctly
- ✅ Volunteer and RSVP systems operational
- ✅ Contact deduplication working properly
- ✅ Calendar and iCal export functioning
- ✅ CSV exports generating correctly

---

## [1.0.0] - 2024-12-29

### Added
- **Custom Post Types:** Registered 6 CPTs (Issues, Events, Endorsements, Team, Volunteers, Press Releases)
- **Volunteer Management:** Complete volunteer signup and management system
  - Volunteer signup forms via shortcode `[cp_volunteer_form]`
  - Skills and availability tracking
  - Status management (New, Contacted, Active)
  - Admin dashboard for volunteer oversight
  - CSV export functionality
- **Event Management:** Comprehensive event and RSVP system
  - RSVP forms via shortcode `[cp_event_rsvp]`
  - Capacity limit tracking
  - Recurring event support (daily, weekly, bi-weekly, monthly)
  - Dietary restriction collection
  - Guest count tracking
  - Admin dashboard for RSVP management
  - CSV export for event attendees
- **Contact Manager:** Centralized contact database
  - Email-based deduplication
  - Integration with volunteer and RSVP systems
  - Single source of truth for all campaign contacts
  - Prevents duplicate contact entries
- **Event Calendar Enhancements:**
  - Calendar grid views (month, week, day) via `[cp_event_calendar]`
  - iCal (.ics) export functionality
  - Google Maps integration via `[cp_event_map]`
  - Event filtering and search
  - Mobile-responsive layouts
- **Database Tables:**
  - `wp_cp_contacts` - Contact management
  - `wp_cp_volunteers` - Volunteer data
  - `wp_cp_event_rsvps` - Event RSVPs
- **Taxonomies:**
  - `issue_category` - Hierarchical taxonomy for Issues
  - `event_type` - Flat taxonomy for Events
- **REST API:** All CPTs enabled for REST API access
- **Translation:** Full internationalization support with `campaign-office-core` text domain
- **Security:**
  - Nonce verification on all forms
  - Input sanitization and output escaping
  - Prepared SQL statements
  - Capability checks for admin features
- **Developer Tools:**
  - Action hooks: `campaign_office_core_loaded`, `cp_volunteer_signup_success`, `cp_event_rsvp_success`
  - Filter hooks: `campaign_office_core_features`
  - Clean singleton pattern implementation
  - Extensible architecture

### Technical Details
- Minimum WordPress: 5.8
- Minimum PHP: 7.4
- Tested up to WordPress: 6.4
- License: GPL v2 or later
- Text Domain: `campaign-office-core`

---

## Version History

| Version | Release Date | Status | Notes |
|---------|-------------|--------|-------|
| 2.0.0 | 2026-01-10 | **Current** | Version alignment, documentation |
| 1.0.0 | 2024-12-29 | Stable | Initial release |

---

## Upgrade Guide

### Upgrading from 1.0.0 to 2.0.0

**This is a safe upgrade with no breaking changes.**

**What Changes:**
- Version number updated to match theme
- Documentation improvements
- Database optimizations (automatic)

**What Stays the Same:**
- All features work identically
- No data loss or migration needed
- Fully backward compatible

**Steps:**
1. Backup your database (recommended)
2. Update the plugin via WordPress admin or manual upload
3. No additional configuration needed!

---

## Roadmap

### Planned for 2.1.0
- [ ] Email notifications for volunteer signups
- [ ] RSVP confirmation emails
- [ ] Bulk volunteer import via CSV
- [ ] Enhanced admin dashboard widgets
- [ ] Volunteer engagement metrics
- [ ] Event attendance statistics

### Future Considerations
- [ ] SMS notifications (Twilio integration)
- [ ] Advanced contact segmentation
- [ ] Email campaign integration
- [ ] Social media post scheduling
- [ ] Predictive volunteer scoring
- [ ] Mobile app companion

---

## Support & Contribution

**GitHub Repository:** [https://github.com/mrwalker511/campaign-office-core](https://github.com/mrwalker511/campaign-office-core)
**Issue Tracker:** [https://github.com/mrwalker511/campaign-office-core/issues](https://github.com/mrwalker511/campaign-office-core/issues)
**Documentation:** See README.md

---

**Maintained by:** Matt Walker
**License:** GPL v2 or later
**Plugin URI:** https://github.com/mrwalker511/campaign-office-core
