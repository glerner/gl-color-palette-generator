<?php
/**
 * Test Performance Monitor Class
 *
 * @package GL_Color_Palette_Generator
 * @subpackage Tests\WP_Mock\Performance
 */

namespace GL_Color_Palette_Generator\Tests\WP_Mock\Performance;

// Check if the class exists in this location or needs to be imported from a different namespace
// Adjust the namespace if needed based on the actual location of the Performance_Monitor class
use GL_Color_Palette_Generator\Performance\Performance_Monitor;
use GL_Color_Palette_Generator\Tests\Base\WP_Mock_Test_Case;
use WP_Mock;
use Mockery;

/**
 * Test class for Performance_Monitor
 *
 * @covers GL_Color_Palette_Generator\Performance\Performance_Monitor
 */
class Test_Performance_Monitor extends WP_Mock_Test_Case {
    protected $performance_monitor;

    public function setUp(): void {
        parent::setUp();
        WP_Mock::setUp();
        $this->performance_monitor = new Performance_Monitor();
    }

    public function test_measure_execution_time(): void {
        $this->performance_monitor->start_measurement('test_operation');
        usleep(100000); // Simulate 100ms operation
        $duration = $this->performance_monitor->end_measurement('test_operation');
        
        $this->assertGreaterThan(0.09, $duration); // Should be around 100ms
        $this->assertLessThan(0.15, $duration); // Allow some overhead
    }

    public function test_memory_usage_tracking(): void {
        $initial_memory = $this->performance_monitor->get_memory_usage();
        
        // Create some memory usage
        $large_array = array_fill(0, 100000, 'test');
        
        $final_memory = $this->performance_monitor->get_memory_usage();
        $this->assertGreaterThan($initial_memory, $final_memory);
    }

    public function test_query_count_tracking(): void {
        $initial_count = $this->performance_monitor->get_query_count();
        
        // Mock global $wpdb
        global $wpdb;
        $wpdb = Mockery::mock('wpdb');
        $wpdb->posts = 'wp_posts';
        $wpdb->users = 'wp_users';
        $wpdb->shouldReceive('get_results')->twice()->andReturn([]);
        
        // Mock query count functions
        WP_Mock::userFunction('get_num_queries', [
            'times' => 2,
            'return' => function() use (&$initial_count) {
                static $calls = 0;
                return $initial_count + $calls++;
            }
        ]);
        
        // Perform some queries
        $wpdb->get_results("SELECT * FROM posts LIMIT 5");
        $wpdb->get_results("SELECT * FROM users LIMIT 5");
        
        $final_count = $this->performance_monitor->get_query_count();
        $this->assertEquals($initial_count + 2, $final_count);
    }

    public function test_performance_threshold_alerts(): void {
        // Test slow operation detection
        $this->performance_monitor->set_threshold('execution_time', 0.05);
        
        $this->performance_monitor->start_measurement('slow_operation');
        usleep(100000); // 100ms operation
        $duration = $this->performance_monitor->end_measurement('slow_operation');
        
        $alerts = $this->performance_monitor->get_alerts();
        $this->assertCount(1, $alerts);
        $this->assertStringContainsString('slow_operation', $alerts[0]);
    }

    public function test_resource_usage_logging(): void {
        // Test logging format
        $this->performance_monitor->log_resource_usage('test_operation');
        
        $logs = $this->performance_monitor->get_logs();
        $this->assertNotEmpty($logs);
        
        $last_log = end($logs);
        $this->assertArrayHasKey('timestamp', $last_log);
        $this->assertArrayHasKey('operation', $last_log);
        $this->assertArrayHasKey('memory_usage', $last_log);
        $this->assertArrayHasKey('query_count', $last_log);
    }

    public function test_concurrent_measurements(): void {
        // Test multiple concurrent operations
        $this->performance_monitor->start_measurement('operation1');
        $this->performance_monitor->start_measurement('operation2');
        
        usleep(50000); // 50ms delay
        $duration1 = $this->performance_monitor->end_measurement('operation1');
        
        usleep(50000); // Another 50ms
        $duration2 = $this->performance_monitor->end_measurement('operation2');
        
        $this->assertGreaterThan($duration1, $duration2);
    }

    public function test_performance_data_aggregation(): void {
        // Test statistics collection
        for ($i = 0; $i < 5; $i++) {
            $this->performance_monitor->start_measurement('repeated_operation');
            usleep(10000); // 10ms operation
            $this->performance_monitor->end_measurement('repeated_operation');
        }
        
        $stats = $this->performance_monitor->get_statistics('repeated_operation');
        $this->assertArrayHasKey('avg_duration', $stats);
        $this->assertArrayHasKey('max_duration', $stats);
        $this->assertArrayHasKey('min_duration', $stats);
        $this->assertArrayHasKey('count', $stats);
        $this->assertEquals(5, $stats['count']);
    }

    public function test_cache_performance_impact(): void {
        // Test performance with and without cache
        $this->performance_monitor->start_measurement('uncached_operation');
        
        // Mock cache functions
        WP_Mock::userFunction('wp_cache_get', [
            'args' => ['test_key'],
            'times' => 1,
            'return' => false
        ]);
        
        WP_Mock::userFunction('wp_cache_set', [
            'args' => ['test_key', 'test_value'],
            'times' => 1,
            'return' => true
        ]);
        
        // Simulate uncached operation - use function in global namespace
        $result1 = \wp_cache_get('test_key');
        if (false === $result1) {
            usleep(50000);
            \wp_cache_set('test_key', 'test_value');
        }
        $uncached_time = $this->performance_monitor->end_measurement('uncached_operation');
        
        $this->performance_monitor->start_measurement('cached_operation');
        
        // Mock second cache get to return a value
        WP_Mock::userFunction('wp_cache_get', [
            'args' => ['test_key'],
            'times' => 1,
            'return' => 'test_value'
        ]);
        
        // Simulate cached operation - use function in global namespace
        $result2 = \wp_cache_get('test_key');
        $cached_time = $this->performance_monitor->end_measurement('cached_operation');
        
        $this->assertGreaterThan($cached_time, $uncached_time);
    }

    public function tearDown(): void {
        WP_Mock::tearDown();
        parent::tearDown();
    }
}
