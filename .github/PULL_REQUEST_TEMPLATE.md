<!--- Title format : ISSUE # : Action-verb driven description-->

## Motivation/Purpose of Changes
<!--- Why is this change needed? Links to existing issues are great. -->
Fixes #

## Proposed Resolution/Implementation
<!--- Describe any implementation choices you made that are noteworthy -->
<!--- or may require discussion. -->

## Screenshot(s)
<!--- (If relevant) -->

## Types of changes
<!--- Put an `x` in all that apply: -->
- [ ] Bug fix
- [ ] New feature
- [ ] Breaking change

## Checklist:
<!--- Go over all the following points, and put an `x` in all the boxes that apply. -->
<!--- If you're unsure about any of these, don't hesitate to ask. We're here to help! -->
<!--- Put an `x` in all the boxes that apply: -->
- [ ] Automated tests pass <!--- If tests don't pass because of a known reason, elaborate on the test and issue -->
- [ ] Code meets syntax standards
- [ ] Namespacing follows [team conventions](https://github.austin.utexas.edu/eis1-wcs/d8-standards/blob/master/Naming_Conventions.md)
- [ ] Change requires a change to the documentation.
  - [ ] I have updated the documentation accordingly.
- [ ] I have added tests to cover my changes.
- [ ] Combo change (this change requires specific changes from another repo). If yes, specify repo and branch:
  - [ ] forty_acres: [`branch`]
  - [ ] utexas_migrate [`branch`]
  - [ ] other: [`update with the repo`: `branch`]
- [ ] This will require a change to an existing migration task
  - [ ] I have created an issue in `utexas_migrate` to document this.

## Reference: installing a site off this branch
<!--- Include installation snippet if multiple repos are involved -->
0. Generate the installation snippet with the branch that corresponds to the PR at https://utdirect.utexas.edu/apps/wcs/wcms/utdk3/environment-generator.

## Reference: running tests locally
0. `fin test web/profiles/contrib/utexas/tests/src/Functional`
0. `fin test-js web/profiles/contrib/utexas/tests/src/FunctionalJavascript`

## Testing steps
0. Perform a thorough code review.
0.
0.

