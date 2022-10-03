<?php

use Nette\Neon\Neon;
use Nette\Utils\FileSystem;
use Symfony\Component\Console\Input\ArgvInput;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Console\Style\SymfonyStyle;

include $_composer_autoload_path ?? __DIR__ . '/../vendor/autoload.php';

$definition = new InputDefinition();
$definition->addOption(new InputOption('help', null, InputOption::VALUE_NONE, 'Display this help message'));
$definition->addOption(new InputOption('config', null, InputOption::VALUE_OPTIONAL, 'Configuration file', 'generator.neon'));
$definition->addArgument(new InputArgument('template', InputArgument::REQUIRED, 'Template for generating'));
$definition->addArgument(new InputArgument('parameter', InputArgument::REQUIRED, 'Parameter for generating by template'));

$output = new ConsoleOutput();

$consoleError = false;
try {
	$input = new ArgvInput(null, $definition);
} catch (\Throwable $e) {
	$consoleError = true;
	$input = new ArgvInput([__FILE__, '--help', '', ''], $definition);
}

$style = new SymfonyStyle($input, $output);

if ($consoleError) {
	$style->error('Console input error, showing help instead.');
}

if ($input->getOption('help')) {
	$list = [];
	foreach ($definition->getArguments() as $arg) {
		$list[] = ['<' . $arg->getName() . '>' => $arg->getDescription()];
	}
	$style->definitionList('Required Arguments', ...$list);

	$list = [];
	foreach ($definition->getOptions() as $opt) {
		$list[] = ['--' . $opt->getName() => $opt->getDescription()];
	}
	$style->definitionList('Avaible options', ...$list);
	echo "\t" . $definition->getSynopsis(false);
	exit;
}

$configFile = $input->getOption('config');
$section = $input->getArgument('template');
$namespace = $input->getArgument('parameter');

$neon = new Neon();
$currentPath = getcwd();

$config = $neon->decodeFile($currentPath . '/' . $configFile);

if (!isset($config['parameters'])) {
	echo 'config.neon should have `parameters` section' . PHP_EOL;
	exit;
}

if (!isset($config['parameters'][$section])) {
	echo 'config.neon should have section `' . $section . '`' . PHP_EOL;
	exit;
}

if (!isset($config['parameters'][$section]['baseFolder'])) {
	echo 'config.neon section `' . $section . '` should have `baseFolder`' . PHP_EOL;
	exit;
}

if (!isset($config['parameters'][$section]['files'])) {
	echo 'config.neon section `' . $section . '` should have `files`' . PHP_EOL;
	exit;
}

$baseNamespace = '';
$baseFolder = $config['parameters'][$section]['baseFolder'];
$configFiles = $config['parameters'][$section]['files'];

if (isset($config['parameters'][$section]['baseNamespace'])) {
	$baseNamespace = $config['parameters'][$section]['baseNamespace'];
}

$namespaceParts = array_map(fn ($e) => ucfirst($e), explode('^', str_replace(['/', '\\'], ['^', '^'], $namespace)));
$lastPart = array_pop($namespaceParts);
$namespacePrefix = implode('\\', $namespaceParts);

$namespacePath = str_replace(
	'/',
	'\\',
	implode('\\', array_filter([$baseNamespace, $namespacePrefix]))
);

$outputPath = str_replace(
	['\\', '/'],
	[DIRECTORY_SEPARATOR, DIRECTORY_SEPARATOR],
	implode(DIRECTORY_SEPARATOR, array_filter([$currentPath, $baseFolder, $namespacePrefix]))
);

echo PHP_EOL;
echo 'Namespace base: ' . $namespacePath . PHP_EOL;
echo 'Output to directory: ' . $outputPath . PHP_EOL;
echo PHP_EOL;

foreach ($configFiles as $targetFileName => $contentFile) {

	if (str_contains($targetFileName, '{name}')) {
		$targetFileName = str_replace('{name}', ucfirst($lastPart), ucfirst($targetFileName));
	} else {
		$targetFileName = ucfirst($lastPart) . ucfirst($targetFileName);
	}

	if (!$content = file_get_contents($currentPath . '/' . $contentFile)) {
		echo 'source could not read: ' . $currentPath . '/' . $contentFile . '' . PHP_EOL;
		continue;
	}

	$content = str_replace(['{#$part0#}', '{#$part1#}'], [$namespacePath, $lastPart], $content);

	$targetFile = $outputPath . DIRECTORY_SEPARATOR . $targetFileName;

	FileSystem::createDir(pathinfo($targetFile, PATHINFO_DIRNAME));

	if (file_exists($targetFile)) {
		echo 'skipped: ' . $targetFile . '' . PHP_EOL;
		continue;
	}

	file_put_contents($targetFile, $content);
	echo 'created: ' . $targetFile . '' . PHP_EOL;
}

$style->success('Done');
exit;
