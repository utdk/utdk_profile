<!--- Title format : ISSUE # : Action-verb driven description-->

## Motivation/Purpose of Changes
<!--- Why is this change needed? Links to existing issues are great. -->
See https://issues.its.utexas.edu/projects/UDK8/issues/UDK8-NNN

## Proposed Resolution/Implementation
<!--- Describe any implementation choices you made that are noteworthy -->
<!--- or may require discussion. -->

## Screenshot(s)
<!--- (If relevant) -->

## Types of changes
<!--- Put an `x` in all the boxes that apply: -->
- [ ] Bug fix (non-breaking change which fixes an issue)
- [ ] New feature (non-breaking change which adds functionality)
- [ ] Breaking change (fix or feature that would cause existing functionality to change)

## Checklist:
<!--- Go over all the following points, and put an `x` in all the boxes that apply. -->
<!--- If you're unsure about any of these, don't hesitate to ask. We're here to help! -->
<!--- Put an `x` in all the boxes that apply: -->
- [ ] Automated tests pass <!--- If tests don't pass because of a known reason, elaborate on the test and issue -->
- [ ] Code meets syntax standards
- [ ] Namespacing follows team conventions
- [ ] Change requires a change to the documentation.
- [ ] I have updated the documentation accordingly.
- [ ] I have added tests to cover my changes.
- [ ] Combo change (this change requires specific changes from another repo, such as  `forty_acres` or `utexas_migrate`). If yes, specify repo and branch:
  - [ ] forty_acres: [`branch`]
  - [ ] utexas_migrate [`branch`]
  - [ ] other: [`update with the repo`: `branch`]

## Testing steps
<!--- Include notes for both functional testing & code review -->
0. `git fetch && git checkout ` this branch
1. `si` (e.g., `alias si='lando drush si utexas -y install_configure_form.enable_update_status_module=NULL --account-mail="wcs-drupal-site-admins@utlists.utexas.edu" --site-mail="wcs-drupal-site-admins@utlists.utexas.edu" --site-name="Drupal Kit Rocks"'`)
2. `lando test web/profiles/utexas/tests/src/Functional`
3. `lando test-js web/profiles/utexas/tests/src/FunctionalJavascript`
0.

## Potential Reviewers

@eis1-wcs/drupal-utdkd8
