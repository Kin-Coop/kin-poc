# CAPTCHA Riddler - Human Question & Answer Captcha

Captcha Riddler is a sub module of Captcha that lets site
administrators create their own questions to foil
automated spam bots.

For a full description of the module, visit the
[project page](https://www.drupal.org/project/riddler).

Submit bug reports and feature suggestions, or track changes in the
[issue queue](https://www.drupal.org/project/issues/riddler).


## Table of contents

- Requirements
- Installation
- Configuration
- Usage
- Maintainers


## Requirements

This module requires the following modules:

- [Captcha](https://www.drupal.org/project/captcha)


## Installation

Install as you would normally install a contributed Drupal module. For further
information, see
[Installing Drupal Modules](https://www.drupal.org/docs/extending-drupal/installing-drupal-modules).


## Configuration

1. To create a new riddle and get an overview of all created riddles head to
   Configuration > People > CAPTCHA Module Settings > Riddler Riddles
   (`/admin/config/people/captcha/riddler-riddle`)

2. Here you have a table of all created riddles, where you can edit, delete and
   translate these riddles.

3. Click 'Add riddle' to add questions that you require users to answer.

4. A random question will be presented to the user on forms as configured in the
   CAPTCHA settings. Allow more than one correct answer on a question by entering a
   comma-separated list in the "Solution" field of a riddle.


## Usage

- To activate the riddler challenge type on a CAPTCHA point, go
  to Configuration > People > CAPTCHA settings > Form settings
  (`/admin/config/people/captcha/captcha-points`), edit the CAPTCHA point of your
  choosing and set the "Challenge type" to "Riddler".

- Alternatively, you can also set the Riddler challenge type as your "Default
  challenge type" under Configuration > People > CAPTCHA Settings
  (`/admin/config/people/captcha`).


## Maintainers

- Thomas Frobieter - [thomas.frobieter](https://www.drupal.org/u/thomasfrobieter)
- Oleksandr Bazyliuk - [alex_optim](https://www.drupal.org/u/alex_optim)
- Aaron Wolfe - [awolfey](https://www.drupal.org/u/awolfey)
- imerlin - [imerlin](https://www.drupal.org/u/imerlin)
- Julian Pustkuchen - [Anybody](https://www.drupal.org/u/anybody)
- Joshua Sedler - [Grevil](https://www.drupal.org/u/grevil)


Supporting organizations:

- [Singlebrook Technology](https://www.drupal.org/singlebrook-technology)
- [Drupal Ukraine Community](https://www.drupal.org/drupal-ukraine-community)
- [GOLEMS GABB](https://www.drupal.org/golems-gabb)
