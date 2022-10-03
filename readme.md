# Generator [![PHP version](https://img.shields.io/badge/PHP-8.0-blue)](https://img.shields.io/badge/PHP-8.0-blue) [![MIT License](https://img.shields.io/badge/license-MIT-green)](https://img.shields.io/badge/license-MIT-green)

Introduction
------------
This tool should help with simple repetitive generation of php files.

Installation
------------

The recommended way to install this helper is through Composer:

`composer global require zbyrih/generator --dev`

Usage
------------

first you have to create config file generator.neon in you current working directory:
```yml
parameters:
	command: # section named command
		baseFolder: App/Commands
		baseNamespace: App\Commands
		files:
			'command{name}.php': generator/command/command.txt # output file : source file
			request.php: generator/command/request.txt
			commandFactory.php: generator/command/commandFactory.txt
```

then run command `generator command Some/Save` that generates files:

App/Commands/Some/CommandSave.php
App/Commands/Some/SaveRequest.php
App/Commands/Some/SaveCommandFactory.php

where it will be replaced in the content, `{#$part0#}` with `Some` and `{#$part1#}` with `Save`.
If the second parameter is longer, for example: `Some/Some/Save`, `{#$part0#}`will be `Some\Some` and `{#$part1#}` will be still `Save`.