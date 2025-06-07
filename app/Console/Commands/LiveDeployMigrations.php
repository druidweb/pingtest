<?php

/**
 * Copyright (C) Health Product Declaration Collaborative, Inc.
 * All Rights Reserved. Email: info@hpd-collaborative.org
 *
 * Proprietary and confidential. Unauthorized copying of this file,
 * via any medium is strictly prohibited.
 */

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class LiveDeployMigrations extends Command
{
  /**
   * The name and signature of the console command.
   *
   * @var string
   */
  protected $signature = 'migrate:update';

  /**
   * The console command description.
   *
   * @var string
   */
  protected $description = 'Update migration table timestamps and migrate.';

  /**
   * Execute the console command.
   */
  public function handle()
  {
    DB::transaction(function () {
      // Step 1: Fetch all rows from the migrations table
      $dbMigrations = DB::table('migrations')->get();

      // Step 2: Retrieve all migration file names
      $fileMigrations = collect(File::files(database_path('migrations')))
        ->mapWithKeys(function ($file) {
          $fileName = $file->getFilename();
          $className = Str::before($fileName, '.php');
          $fileName = $className;
          $baseName = preg_replace('/\d{4}_\d{2}_\d{2}_\d{6}_/', '', $className);

          return [$baseName => $fileName];
        });

      // Step 3: Compare and update timestamps
      foreach ($dbMigrations as $dbMigration) {
        $baseName = preg_replace('/\d{4}_\d{2}_\d{2}_\d{6}_/', '', Str::before($dbMigration->migration, '.php'));

        if (isset($fileMigrations[$baseName])) {
          echo 'Updating '.$fileMigrations[$baseName]."\n";
          DB::table('migrations')
            ->where('id', $dbMigration->id)
            ->update(['migration' => $fileMigrations[$baseName]]);
        }
      }
    });

    // Step 4: Clear optimized files
    $this->call('optimize:clear');

    // Step 5: Get a fresh connection
    DB::reconnect();

    // Step 6: Migrate new migration files
    $this->call('migrate', [
      '--force' => true,
      '--path' => database_path('migrations'),
      '--realpath' => true,
    ]);

    $this->info('Migration timestamps updated and migrations run successfully.');
  }
}
