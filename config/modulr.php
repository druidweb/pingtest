<?php

return [

  /**
   * MODULES NAMESPACE
   *
   * This is the PHP namespace that your modules will be created in. For
   * example, a module called "Helpers" will be placed in \Modules\Helpers
   * by default.
   *
   * It is *highly recommended* that you configure this to your organization
   * name to make extracting modules to their own package easier (should you
   * choose to ever do so).
   *
   * If you set the namespace, you should also set the vendor name to match.
   */
  'modules_namespace' => 'Druid',

  /**
   * COMPOSER "VENDOR" NAME
   *
   * This is the prefix used for your composer.json file. This should be the
   * kebab-case version of your module namespace (if left null, we will
   * generate the kebab-case version for you).
   */
  'modules_vendor' => 'druidweb',

  /**
   * MODULES DIRECTORY
   *
   * If you want to install modules in a custom directory, you can do so here.
   */
  'modules_directory' => 'modules',

  /**
   * BASE TEST CASE
   *
   * This is the base TestCase class name that auto-generated Tests should
   * extend. By default, it assumes the default \Tests\TestCase exists.
   */
  'tests_base' => 'Tests\TestCase',

  /**
   * CUSTOM STUBS
   *
   * If you would like to use your own custom stubs for new modules, you can
   * configure those here. This should be an array where the key is the path
   * relative to the module and the value is the absolute path to the stub
   * file. Destination paths and contents support placeholders. See the
   * README.md file for more information.
   *
   * For example:
   *
   * 'stubs' => [
   * 	'src/Providers/StubClassNamePrefixServiceProvider.php' => base_path('stubs/modules/ServiceProvider.php'),
   * ],
   */
  'stubs' => null,
];
