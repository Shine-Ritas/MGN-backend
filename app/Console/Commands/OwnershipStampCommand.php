<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use RegexIterator;

class OwnershipStampCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ownership:stamp {newOwner?} {--directory=app/Models : Directory to process} {--dry-run : Show what would be changed without making changes}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Add or update ownership headers in PHP files. Use with new owner name to replace existing owner.';

    /**
     * Default owner information
     */
    private const DEFAULT_OWNER = '@Htet_Shine';
    private const DEFAULT_EMAIL = 'whoishsh@gmail.com';
    private const PROJECT_NAME = 'MGN-Backend';

    /**
     * Directories to exclude from processing
     */
    private const EXCLUDED_DIRECTORIES = [
        'vendor',
        'public', 
        'storage',
        'bootstrap/cache',
        'node_modules'
    ];

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $newOwner = $this->argument('newOwner');
        $directory = $this->option('directory');
        $dryRun = $this->option('dry-run');

        if ($newOwner) {
            $this->info("Replacing owner name to: @{$newOwner}");
        } else {
            $this->info("Adding ownership headers with default owner: " . self::DEFAULT_OWNER);
        }

        if ($dryRun) {
            $this->warn("DRY RUN MODE - No files will be modified");
        }

        $basePath = base_path($directory);
        
        if (!File::exists($basePath)) {
            $this->error("Directory does not exist: {$directory}");
            return 1;
        }

        $this->info("Processing directory: {$directory}");
        
        $files = $this->getPhpFiles($basePath);
        $processedCount = 0;
        $modifiedCount = 0;

        foreach ($files as $file) {
            if ($this->shouldSkipFile($file)) {
                continue;
            }

            $processedCount++;
            $modified = $this->processFile($file, $newOwner, $dryRun);
            
            if ($modified) {
                $modifiedCount++;
                $relativePath = str_replace(base_path() . '/', '', $file);
                $this->line("✓ {$relativePath}");
            }
        }

        $this->info("\nProcessing complete!");
        $this->info("Files processed: {$processedCount}");
        $this->info("Files modified: {$modifiedCount}");

        return 0;
    }

    /**
     * Get all PHP files in the given directory
     */
    private function getPhpFiles(string $directory): array
    {
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($directory, RecursiveDirectoryIterator::SKIP_DOTS)
        );
        
        $phpFiles = new RegexIterator($iterator, '/^.+\.php$/i', RegexIterator::MATCH);
        
        $files = [];
        foreach ($phpFiles as $file) {
            $files[] = $file->getRealPath();
        }
        
        return $files;
    }

    /**
     * Check if file should be skipped
     */
    private function shouldSkipFile(string $file): bool
    {
        $relativePath = str_replace(base_path() . '/', '', $file);
        
        foreach (self::EXCLUDED_DIRECTORIES as $excludedDir) {
            if (str_starts_with($relativePath, $excludedDir . '/')) {
                return true;
            }
        }
        
        return false;
    }

    /**
     * Process a single file
     */
    private function processFile(string $file, ?string $newOwner, bool $dryRun): bool
    {
        $content = File::get($file);
        $originalContent = $content;

        // If new owner is provided, replace existing ownership
        if ($newOwner) {
            $content = $this->replaceOwnership($content, $newOwner);
        } else {
            $content = $this->addOwnershipHeader($content);
        }

        // Check if content was modified
        if ($content === $originalContent) {
            return false;
        }

        // Write changes if not in dry-run mode
        if (!$dryRun) {
            File::put($file, $content);
        }

        return true;
    }

    /**
     * Add ownership header to file if it doesn't exist
     */
    private function addOwnershipHeader(string $content): string
    {
        // Check if ownership header already exists
        if (str_contains($content, 'Project: ' . self::PROJECT_NAME)) {
            return $content;
        }

        $header = $this->generateOwnershipHeader(self::DEFAULT_OWNER, self::DEFAULT_EMAIL);

        // Check if file starts with <?php
        if (str_starts_with(trim($content), '<?php')) {
            // Replace the opening PHP tag with tag + header
            $content = preg_replace(
                '/^<\?php\s*/',
                "<?php\n{$header}\n",
                $content,
                1
            );
        } else {
            // Prepend header to the beginning
            $content = "<?php\n{$header}\n\n" . $content;
        }

        return $content;
    }

    /**
     * Replace existing ownership information
     */
    private function replaceOwnership(string $content, string $newOwner): string
    {
        // Pattern to match ownership headers - more flexible pattern
        $pattern = '/\/\*\*\s*\n\s*\*\s*Project:\s*' . preg_quote(self::PROJECT_NAME, '/') . '\s*\n\s*\*\s*Owner:\s*@\w+\s*\n\s*\*\s*Email:.*?\n\s*\*\s*\n\s*\*\s*This file is part of the proprietary source code owned by @\w+\.\s*\n\s*\*\s*Unauthorized copying, distribution, or modification is prohibited\.\s*\n\s*\*\//s';
        
        if (preg_match($pattern, $content)) {
            // Replace existing header
            $newHeader = $this->generateOwnershipHeader("@{$newOwner}", self::DEFAULT_EMAIL);
            $content = preg_replace($pattern, $newHeader, $content);
        } else {
            // Look for any @owner pattern and replace it
            $content = preg_replace('/@' . preg_quote(trim(self::DEFAULT_OWNER, '@'), '/') . '/', "@{$newOwner}", $content);
        }

        return $content;
    }

    /**
     * Generate ownership header
     */
    private function generateOwnershipHeader(string $owner, string $email): string
    {
        return "/**
 * Project: " . self::PROJECT_NAME . "
 * Owner: {$owner}
 * Email: {$email}
 * 
 * This file is part of the proprietary source code owned by {$owner}.
 * Unauthorized copying, distribution, or modification is prohibited.
 */";
    }
}
