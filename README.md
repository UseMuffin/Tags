# Tags

[![Build Status](https://img.shields.io/travis/UseMuffin/Tags/master.svg?style=flat-square)](https://travis-ci.org/UseMuffin/Tags)
[![Coverage](https://img.shields.io/coveralls/UseMuffin/Tags/master.svg?style=flat-square)](https://coveralls.io/r/UseMuffin/Tags)
[![Total Downloads](https://img.shields.io/packagist/dt/muffin/tags.svg?style=flat-square)](https://packagist.org/packages/muffin/tags)
[![License](https://img.shields.io/badge/license-MIT-blue.svg?style=flat-square)](LICENSE)

{{@TODO description}}

## Install

Using [Composer][composer]:

```
composer require muffin/tags:dev-master
```

You then need to load the plugin. In `boostrap.php`, something like:

```php
\Cake\Core\Plugin::load('Muffin/Tags');
```

## Usage

{{@TODO documentation}}

## Quick Start Guide

You need to add the column *tag_count* to the taggable table
add the behavior 

```php
$this->addBehavior('Muffin/Tags.Tag');
```

and in the view

```php
echo $this->Form->input('tags');
```

Enjoy tagging

## Patches & Features

* Fork
* Mod, fix
* Test - this is important, so it's not unintentionally broken
* Commit - do not mess with license, todo, version, etc. (if you do change any, bump them into commits of
their own that I can ignore when I pull)
* Pull request - bonus point for topic branches

To ensure your PRs are considered for upstream, you MUST follow the CakePHP coding standards.

## Bugs & Feedback

http://github.com/usemuffin/tags/issues

## License

Copyright (c) 2015, [Use Muffin] and licensed under [The MIT License][mit].

[cakephp]:http://cakephp.org
[composer]:http://getcomposer.org
[mit]:http://www.opensource.org/licenses/mit-license.php
[muffin]:http://usemuffin.com
