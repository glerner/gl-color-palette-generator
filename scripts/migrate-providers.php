<?php
/**
 * Migration script to standardize provider directory naming
 *
 * @package    GLColorPalette
 * @author     George Lerner
 * @link       https://website-tech.glerner.com/
 * @since      1.0.0
 */

class ProvidersDirectoryMigration {
    private $rootDir;
    private $oldProvidersDir;
    private $newProvidersDir;
    private $backupDir;

    public function __construct() {
        $this->rootDir = dirname(__DIR__);
        $this->oldProvidersDir = $this->rootDir . '/includes/providers';
        $this->newProvidersDir = $this->rootDir . '/includes/Providers';
        $this->backupDir = $this->rootDir . '/includes/providers_backup_' . date('Y-m-d_His');
    }

    public function run() {
        try {
            // Create backup
            if (is_dir($this->oldProvidersDir)) {
                $this->createBackup();
            }

            // Create new directory if it doesn't exist
            if (!is_dir($this->newProvidersDir)) {
                mkdir($this->newProvidersDir, 0755, true);
            }

            // Move files from old to new directory
            if (is_dir($this->oldProvidersDir)) {
                $this->moveFiles();
            }

            // Update namespace references in files
            $this->updateNamespaces();

            // Clean up old directory
            if (is_dir($this->oldProvidersDir)) {
                $this->cleanup();
            }

            echo "Migration completed successfully.\n";
        } catch (Exception $e) {
            echo "Error: " . $e->getMessage() . "\n";
            $this->rollback();
        }
    }

    private function createBackup() {
        if (!is_dir($this->backupDir)) {
            mkdir($this->backupDir, 0755, true);
        }
        $this->recursiveCopy($this->oldProvidersDir, $this->backupDir);
    }

    private function moveFiles() {
        $this->recursiveCopy($this->oldProvidersDir, $this->newProvidersDir);
    }

    private function updateNamespaces() {
        $files = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($this->newProvidersDir)
        );

        foreach ($files as $file) {
            if ($file->isFile() && $file->getExtension() === 'php') {
                $content = file_get_contents($file->getPathname());
                $content = str_replace(
                    'namespace GLColorPalette\providers',
                    'namespace GLColorPalette\Providers',
                    $content
                );
                file_put_contents($file->getPathname(), $content);
            }
        }
    }

    private function cleanup() {
        $this->recursiveDelete($this->oldProvidersDir);
    }

    private function rollback() {
        if (is_dir($this->backupDir)) {
            if (is_dir($this->newProvidersDir)) {
                $this->recursiveDelete($this->newProvidersDir);
            }
            rename($this->backupDir, $this->oldProvidersDir);
        }
    }

    private function recursiveCopy($src, $dst) {
        $dir = opendir($src);
        if (!is_dir($dst)) {
            mkdir($dst, 0755, true);
        }
        while (($file = readdir($dir)) !== false) {
            if ($file === '.' || $file === '..') {
                continue;
            }
            $srcFile = $src . '/' . $file;
            $dstFile = $dst . '/' . $file;
            if (is_dir($srcFile)) {
                $this->recursiveCopy($srcFile, $dstFile);
            } else {
                copy($srcFile, $dstFile);
            }
        }
        closedir($dir);
    }

    private function recursiveDelete($dir) {
        if (is_dir($dir)) {
            $files = scandir($dir);
            foreach ($files as $file) {
                if ($file === '.' || $file === '..') {
                    continue;
                }
                $path = $dir . '/' . $file;
                if (is_dir($path)) {
                    $this->recursiveDelete($path);
                } else {
                    unlink($path);
                }
            }
            rmdir($dir);
        }
    }
}

// Run migration
$migration = new ProvidersDirectoryMigration();
$migration->run(); 
