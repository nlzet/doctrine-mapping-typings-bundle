Doctrine mapping typings generator
==================================

## About

This is a symfony bundle to integrate [nlzet/doctrine-mapping-typings](https://github.com/nlzet/doctrine-mapping-typings) into your symfony project.
See project for more information.

## Installation

Install with composer:

```bash
composer require nlzet/doctrine-mapping-typings-bundle
```

## Configuration

Full configuration example:

```yaml
# Bundle configuration for nlzet/doctrine-mapping-typings-bundle
# note: All "\" characters are already stripped from the class names.
nlzet_doctrine_mapping_typings:
    # add regex patterns starting with a / or a partial match to exclude from the mapping.
    exclude_patterns:
    - '/[Cc]ache/'
    - 'DoctrineMigrations'
    # add key-value pairs to map a class to a different class.
    class_aliases:
        GedmoTranslatorTranslation: 'GedmoTranslation'
    # add key-value pairs to replace parts of the class name.
    # note: "\" characters are already stripped.
    class_replacements:
        Entity: ''
        Model: ''
        Bundle: ''
    # only output properties that are exposed through JMS Serializer Expose/Exclude and ExclusionPolicy.
    only_exposed: true
```

## Usage

### Commands

### About command

This command will show all mapped/filtered entities and show the target typings name.
All configuration options are available as command line options (when no options are passed, the bundle configuration values take precedence).

```bash
php bin/console nlzet:doctrine-typings:about
```

### Convert command

All configuration options are available as command line options (when no options are passed, the bundle configuration values take precedence).

```bash
php bin/console nlzet:doctrine-typings:convert output/doctrine-mapping-typings.ts
```
