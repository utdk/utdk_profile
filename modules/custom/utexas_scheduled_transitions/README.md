# Scheduled Transitions - Hour Only Restriction

Restricts scheduled content moderation transitions to top-of-hour times (00 minutes, 00 seconds).

## Purpose

When using the [Scheduled Transitions](https://www.drupal.org/project/scheduled_transitions) module, this module enforces that all scheduled transitions must occur at the top of the hour (e.g., 2:00 PM, not 2:15 PM or 2:30 PM).

## How It Works

The module provides **three layers of validation**:

1. **Client-side normalization (JavaScript)** — When an editor selects a time, minutes and seconds are automatically set to 00:00.
2. **HTML5 attribute** — The datetime input's `step` attribute is set to 3600 seconds (1 hour), which some browsers respect.
3. **Server-side validation** — The form validation handler rejects any submission where minutes or seconds are not 00.

This ensures that even if an editor somehow bypasses the client-side behavior (e.g., via browser dev tools or direct API calls), the server rejects invalid times.

## Installation

1. Enable the module:
   ```bash
   drush en utexas_scheduled_transitions
   ```

2. Clear caches:
   ```bash
   drush cr
   ```

## Usage

Once enabled, the module automatically restricts the "Schedule transition" form on all scheduled transitions. Editors will see:

- A datetime picker that automatically rounds to the top of the hour
- A validation error if they try to submit with non-zero minutes/seconds

## Customization

To modify the error message, edit the message in:
- `utexas_scheduled_transitions.module` (function: `_utexas_scheduled_transitions_validate_time`)
- `src/Plugin/Validation/Constraint/HourOnlyTimeConstraint.php` (property: `public $message`)

## File Structure

```
utexas_scheduled_transitions/
├── utexas_scheduled_transitions.info.yml     # Module metadata
├── utexas_scheduled_transitions.module        # Form alteration & validation
├── utexas_scheduled_transitions.libraries.yml # JavaScript library definition
├── js/
│   └── datetime-hour-restrict.js                     # Client-side behavior
├── src/Plugin/Validation/Constraint/
│   ├── HourOnlyTimeConstraint.php                    # Constraint definition
│   └── HourOnlyTimeConstraintValidator.php           # Constraint validator
└── README.md                                         # This file
```

## Testing

To test the module:

1. Enable it on your site
2. Navigate to a node's "Scheduled transitions" tab
3. Click "Add" to schedule a transition
4. Select a datetime with non-zero minutes (e.g., 2:15 PM)
5. Submit the form
6. Observe:
   - JavaScript automatically corrects to 2:00 PM (client-side)
   - If JS is disabled, server validation rejects with an error message

## Requirements

- Drupal 10+ or 11+
- Scheduled Transitions module
- No additional dependencies

## Notes

The form_id detection uses pattern matching to handle dynamically generated form IDs:
- Matches any form with 'scheduled_transition' and 'add' in the form_id
- This catches variations like `scheduled_transition_add_form`, `node_page_scheduled_transitions_add_form`, etc.
