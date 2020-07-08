---
name: Release
about: Prepare code for a new release
labels: 'release'

---

## Pre-release checks
- [ ] Review the [issues resolved](https://github.austin.utexas.edu/eis1-wcs/utdk_profile/milestones) since the last release and ensure that all changes have accommodated configuration for new and existing sites
- [ ] Review the documentation issues for any pending tasks that relate to the issues resolved; if any have not been completed, put this issue on hold & resolve those documentation tasks

## Release pull request tasks
- [ ] Create release branch from develop, e.g. `release/3.0.0-beta.x`
- [ ] Bump version number in `utexas.info.yml`
- [ ] Update `forty_acres` version in `composer.json` to point to the latest release and not `dev-develop`
- [ ] Open PR for release branch

## Release completion tasks
- [ ] After approval, merge release branch to develop & master:
- Merge using the Gitflow strategy:
    - `git fetch && git checkout develop && git pull origin develop && git merge --no-ff release/<version number>`
    - `git fetch && git checkout master && git pull origin master && git merge --no-ff release/<version number>`
    - `git tag -a <version number>`
    - `git push origin develop && git push origin master && git push origin <version number>`
- [ ] [Create a new release](https://github.austin.utexas.edu/eis1-wcs/utdk_profile/releases/new) (version number and release title should be the same (e.g., `3.0.0-beta.10`)
- [ ] Use [gren](https://github.com/github-tools/github-release-notes) generate the release notes `gren release --api-url=https://github.austin.utexas.edu/api/v3 --repo=utdk_profile --username=eis1-wcs --ignore-issues-with="wontfix,release,duplicate,invalid" --override`
- [ ] Create a documentation release on https://github.austin.utexas.edu/eis1-wcs/utdk_docs (further instructions there)
