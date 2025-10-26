<?php

namespace App\Console\Commands;

use App\Models\Mogou;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class DataTransformationCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'transform:data';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'use this command to transform data strucutre to desired structure';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        Mogou::each(function ($mogou) {
            $mogou->subMogous($mogou->rotation_key)->each(function ($subMogou) {
                $subMogou->update([
                    'slug' => Str::slug($subMogou->title).'-'.$subMogou->ulid,
                ]);
            });
            
            $this->info('Mogou slug updated: '.$mogou->slug);
        });

        $this->info('Data transformation completed successfully.');
    }
}
