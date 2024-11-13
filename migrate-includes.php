<?php
/**
 * Migration script to consolidate includes directories
 * Run from project root: php migrate-includes.php
 */

class IncludesMigration {
    private $rootDir;
    private $sourceIncludes;
    private $targetIncludes;
    private $backupDir;
    private $log = [];

    public function __construct() {
        $this->rootDir = dirname(__FILE__);
        $this->sourceIncludes = $this->rootDir . '/includes';
        $this->targetIncludes = $this->rootDir . '/gl-color-palette-generator/includes';
        $this->backupDir = $this->rootDir . '/includes_backup_' . date('Y-m-d_His');
    }

    public function run() {
        try {
            $this->validateDirectories();
            $this->createBackup();
            $this->createTargetStructure();
            $this->migrateFiles();
            $this->updateFilePaths();
            $this->cleanup();
            $this->outputLog();
        } catch (Exception $e) {
            echo "Error: " . $e->getMessage() . "\n";
            $this->rollback();
        }
    }

    private function validateDirectories() {
        if (!is_dir($this->sourceIncludes)) {
            throw new Exception("Source includes directory not found");
        }
        if (!is_dir($this->targetIncludes)) {
            throw new Exception("Target includes directory not found");
        }
    }

    private function createBackup() {
        $this->log[] = "Creating backup...";
        mkdir($this->backupDir);
        $this->recursiveCopy($this->sourceIncludes, $this->backupDir);
        $this->log[] = "Backup created at: " . $this->backupDir;
    }

    private function createTargetStructure() {
        $directories = ['abstracts', 'interfaces', 'classes', 'traits'];
        foreach ($directories as $dir) {
            $path = $this->targetIncludes . '/' . $dir;
            if (!is_dir($path)) {
                mkdir($path, 0755, true);
                $this->log[] = "Created directory: $path";
            }
        }
    }

    private function migrateFiles() {
        $this->log[] = "Starting file migration...";

        // Migrate categorized files
        $this->migrateDirectory('abstracts');
        $this->migrateDirectory('interfaces');
        $this->migrateDirectory('classes');
        $this->migrateDirectory('traits');

        // Migrate root files
        $rootFiles = glob($this->sourceIncludes . '/*.php');
        foreach ($rootFiles as $file) {
            $filename = basename($file);
            $target = $this->targetIncludes . '/' . $filename;

            // Check for duplicates
            if (file_exists($target)) {
                $this->handleDuplicate($file, $target);
            } else {
                rename($file, $target);
                $this->log[] = "Moved: $filename";
            }
        }
    }

    private function migrateDirectory($dirName) {
        $sourceDir = $this->sourceIncludes . '/' . $dirName;
        if (!is_dir($sourceDir)) {
            return;
        }

        $files = glob($sourceDir . '/*.php');
        foreach ($files as $file) {
            $filename = basename($file);
            $target = $this->targetIncludes . '/' . $dirName . '/' . $filename;

            if (file_exists($target)) {
                $this->handleDuplicate($file, $target);
            } else {
                rename($file, $target);
                $this->log[] = "Moved: $dirName/$filename";
            }
        }
    }

    private function handleDuplicate($source, $target) {
        // Compare files
        if (md5_file($source) === md5_file($target)) {
            unlink($source); // Files are identical, remove source
            $this->log[] = "Removed duplicate: " . basename($source);
        } else {
            // Files differ - keep both with different names
            $newName = $target . '.merged';
            rename($source, $newName);
            $this->log[] = "WARNING: File conflict - saved as: " . basename($newName);
        }
    }

    private function updateFilePaths() {
        $this->log[] = "Updating file paths...";
        $files = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($this->targetIncludes)
        );

        foreach ($files as $file) {
            if ($file->isFile() && $file->getExtension() === 'php') {
                $content = file_get_contents($file->getRealPath());
                $updated = false;

                // Update require/include paths
                $patterns = [
                    '~require(_once)?\s+[\'"]' . preg_quote($this->sourceIncludes, '~') . '~',
                    '~include(_once)?\s+[\'"]' . preg_quote($this->sourceIncludes, '~') . '~'
                ];

                foreach ($patterns as $pattern) {
                    $newContent = preg_replace($pattern, '$0' . str_replace($this->sourceIncludes, $this->targetIncludes, ''), $content);
                    if ($newContent !== $content) {
                        $content = $newContent;
                        $updated = true;
                    }
                }

                if ($updated) {
                    file_put_contents($file->getRealPath(), $content);
                    $this->log[] = "Updated paths in: " . $file->getFilename();
                }
            }
        }
    }

    private function cleanup() {
        if (is_dir($this->sourceIncludes)) {
            $this->removeDirectory($this->sourceIncludes);
            $this->log[] = "Removed old includes directory";
        }
    }

    private function removeDirectory($dir) {
        if (!is_dir($dir)) {
            return;
        }

        $files = array_diff(scandir($dir), ['.', '..']);
        foreach ($files as $file) {
            $path = $dir . '/' . $file;
            is_dir($path) ? $this->removeDirectory($path) : unlink($path);
        }
        rmdir($dir);
    }

    private function recursiveCopy($src, $dst) {
        $dir = opendir($src);
        @mkdir($dst);
        while (($file = readdir($dir))) {
            if (($file != '.') && ($file != '..')) {
                if (is_dir($src . '/' . $file)) {
                    $this->recursiveCopy($src . '/' . $file, $dst . '/' . $file);
                } else {
                    copy($src . '/' . $file, $dst . '/' . $file);
                }
            }
        }
        closedir($dir);
    }

    private function rollback() {
        $this->log[] = "Rolling back changes...";
        if (is_dir($this->backupDir)) {
            $this->recursiveCopy($this->backupDir, $this->sourceIncludes);
            $this->removeDirectory($this->backupDir);
            $this->log[] = "Rollback complete";
        }
    }

    private function outputLog() {
        echo "\nMigration Log:\n";
        echo "==============\n";
        foreach ($this->log as $entry) {
            echo "$entry\n";
        }
    }
}

// Run the migration
$migration = new IncludesMigration();
$migration->run(); 
