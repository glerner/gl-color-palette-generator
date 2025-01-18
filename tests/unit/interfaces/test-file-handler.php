<?php
/**
 * File Handler Interface Tests
 *
 * @package GL_Color_Palette_Generator
 * @subpackage Tests\Interfaces
 * @since 1.0.0
 */

namespace GL_Color_Palette_Generator\Tests\Unit\Interfaces;

use GL_Color_Palette_Generator\Tests\Base\Unit_Test_Case;
use GL_Color_Palette_Generator\Interfaces\FileHandler;

class Test_FileHandler extends Unit_Test_Case {
    private $handler;

    public function setUp(): void {
        $this->handler = $this->createMock(FileHandler::class);
    }

    public function test_read_file_returns_string(): void {
        // Arrange
        $path = '/path/to/test/file.txt';
        $expected = 'Test file content';

        $this->handler
            ->expects($this->once())
            ->method('read_file')
            ->with($path)
            ->willReturn($expected);

        // Act
        $result = $this->handler->read_file($path);

        // Assert
        $this->assertIsString($result);
        $this->assertEquals($expected, $result);
    }

    public function test_write_file_returns_true_on_success(): void {
        // Arrange
        $path = '/path/to/test/file.txt';
        $content = 'Test content to write';

        $this->handler
            ->expects($this->once())
            ->method('write_file')
            ->with($path, $content)
            ->willReturn(true);

        // Act
        $result = $this->handler->write_file($path, $content);

        // Assert
        $this->assertTrue($result);
    }

    public function test_delete_file_returns_true_on_success(): void {
        // Arrange
        $path = '/path/to/test/file.txt';

        $this->handler
            ->expects($this->once())
            ->method('delete_file')
            ->with($path)
            ->willReturn(true);

        // Act
        $result = $this->handler->delete_file($path);

        // Assert
        $this->assertTrue($result);
    }

    public function test_file_exists_returns_true_for_existing_file(): void {
        // Arrange
        $path = '/path/to/test/file.txt';

        $this->handler
            ->expects($this->once())
            ->method('file_exists')
            ->with($path)
            ->willReturn(true);

        // Act
        $result = $this->handler->file_exists($path);

        // Assert
        $this->assertTrue($result);
    }

    public function test_get_file_info_returns_array(): void {
        // Arrange
        $path = '/path/to/test/file.txt';
        $expected = [
            'name' => 'file.txt',
            'size' => 1024,
            'modified' => '2024-12-08 18:26:54',
            'type' => 'text/plain',
            'permissions' => 0644
        ];

        $this->handler
            ->expects($this->once())
            ->method('get_file_info')
            ->with($path)
            ->willReturn($expected);

        // Act
        $result = $this->handler->get_file_info($path);

        // Assert
        $this->assertIsArray($result);
        $this->assertArrayHasKey('name', $result);
        $this->assertArrayHasKey('size', $result);
        $this->assertArrayHasKey('modified', $result);
        $this->assertEquals($expected, $result);
    }

    public function test_create_directory_returns_true_on_success(): void {
        // Arrange
        $path = '/path/to/test/directory';
        $permissions = 0755;

        $this->handler
            ->expects($this->once())
            ->method('create_directory')
            ->with($path, $permissions)
            ->willReturn(true);

        // Act
        $result = $this->handler->create_directory($path, $permissions);

        // Assert
        $this->assertTrue($result);
    }

    public function test_list_directory_returns_array(): void {
        // Arrange
        $path = '/path/to/test/directory';
        $expected = [
            'files' => ['file1.txt', 'file2.txt'],
            'directories' => ['dir1', 'dir2']
        ];

        $this->handler
            ->expects($this->once())
            ->method('list_directory')
            ->with($path)
            ->willReturn($expected);

        // Act
        $result = $this->handler->list_directory($path);

        // Assert
        $this->assertIsArray($result);
        $this->assertArrayHasKey('files', $result);
        $this->assertArrayHasKey('directories', $result);
        $this->assertEquals($expected, $result);
    }

    /**
     * @dataProvider invalidPathProvider
     */
    public function test_read_file_throws_exception_for_invalid_path($path): void {
        $this->handler
            ->expects($this->once())
            ->method('read_file')
            ->with($path)
            ->willThrowException(new \InvalidArgumentException());

        $this->expectException(\InvalidArgumentException::class);
        $this->handler->read_file($path);
    }

    /**
     * @dataProvider invalidContentProvider
     */
    public function test_write_file_throws_exception_for_invalid_content($content): void {
        $this->handler
            ->expects($this->once())
            ->method('write_file')
            ->with('/path/to/file.txt', $content)
            ->willThrowException(new \InvalidArgumentException());

        $this->expectException(\InvalidArgumentException::class);
        $this->handler->write_file('/path/to/file.txt', $content);
    }

    public function invalidPathProvider(): array {
        return [
            'empty path' => [''],
            'invalid characters' => ['path/with/invalid/*/chars'],
            'absolute path with drive' => ['C:\\path\\to\\file'],
            'too long path' => [str_repeat('/a', 4096)],
            'non-string path' => [42]
        ];
    }

    public function invalidContentProvider(): array {
        return [
            'null content' => [null],
            'array content' => [[]],
            'object content' => [new \stdClass()],
            'resource content' => [fopen('php://memory', 'r')],
            'callable content' => [function() {}]
        ];
    }
}
