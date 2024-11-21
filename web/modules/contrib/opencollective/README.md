# Open Collective

A collection of modules for integrating [Open Collective](https://opencollective.com) with Drupal.

## Submodules

Most modules included provide very specific integrations. The goal is to keep each submodule very focused, and only dependent on what it strictly needs.

### Open Collective - Fields

Provides simple Field related plugins for displaying [Open Collective widgets](https://docs.opencollective.com/help/collectives/widgets) and [Contribution Flow iframes](https://docs.opencollective.com/help/collectives/contribution-flow) within Drupal.

### Open Collective - Api

Provides a developer service for querying [Open Collective's GraphQL api](https://graphql-docs-v2.opencollective.com/welcome).

Provides a new Plugin type for creating dynamic GraphQL Queries.

### Open Collective - Api Fields

Providers simple Field plugins for displaying templated custom Api query results.

### Open Collective - Funding

Funding Providers for the [Funding](https://www.drupal.org/project/funding) module to display Open Collective widgets and embeds.

### Open Collective - Api Funding

Funding Providers for the [Funding](https://www.drupal.org/project/funding) module to display templated custom Api query results.

### Open Collective - OpenId Connect

Provides an Client for the [OpenId Connect](https://www.drupal.org/project/openid_connect) module so that your visitors can log into your Drupal site with Open Collective.

### Open Collective - Webhooks

Provides a secure endpoint for Drupal to accept webhook payloads from Open Collective. Read more about [Open Collective Webhooks](https://docs.opencollective.com/help/collectives/collective-settings/integrations).

Upon receiving a webhook payload, this module will dispatch PHP (and optionally, JS) events.

### Open Collective - Commerce Payment

Coming soon...
