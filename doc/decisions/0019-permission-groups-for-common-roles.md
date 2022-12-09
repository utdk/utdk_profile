# 19. Permission groups for common roles

Date: 2022-12-05

## Status

Accepted

## Context

It is a truth universally acknowledged that different sites will have different types of authenticated users requiring different permission groups. We also acknowledge that Drupal permissions are complicated and that our team has a role in helping site managers assign the correct permissions to their authenticated users. In Drupal Kit 2, we tried to accommodate this by providing granular roles with predefined permissions sets: the `Standard Page Editor` could perform content editing for the `Standard Page` content type, and so on. People who had to manage users on their sites generally found this approach confusing. We need a method for assigning common permission sets that is both easy to use and flexible.

## Decision

Let sites control which permissions are assigned to Drupal roles. Provide a user interface and a command-line tool for assigning the most common editorial and managerial permission sets.

## Consequences

- Sites can create as many or as few roles as is appropriate for their needs, and can easily apply common permission sets without having to worry whether they are granting too much permission or missing important permissions.
- "UTDK Managed" sites can have a common role schema with consistent permissions across all sites.
