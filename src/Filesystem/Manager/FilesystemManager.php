<?php
declare(strict_types=1);

namespace Boruta\Timebase\Filesystem\Manager;


use Boruta\Timebase\Common\Exception\EmptyDatabaseException;
use Boruta\Timebase\Filesystem\Constant\ExtensionConstant;
use Boruta\Timebase\Filesystem\Entity\FileSearchResultEntity;
use Boruta\Timebase\Filesystem\Helper\FilenameHelper;
use Boruta\Timebase\Filesystem\Helper\PathHelper;
use Boruta\Timebase\Filesystem\Helper\StructureHelper;
use Boruta\Timebase\Filesystem\Query\SearchInFileQuery;
use DateTime;
use DateTimeZone;
use Exception;
use Psr\Log\LoggerInterface;

/**
 * Class FilesystemManager
 * @package Boruta\Timebase\Filesystem\Manager
 */
class FilesystemManager
{
    /**
     * @var string
     */
    private $path;
    /**
     * @var LoggerInterface|null
     */
    private $logger;

    /**
     * FilesystemManager constructor.
     * @param string $path
     * @param LoggerInterface|null $logger
     */
    public function __construct(string $path, LoggerInterface $logger = null)
    {
        $this->path = $path;
        $this->logger = $logger;
    }

    /**
     * @param array $storage
     * @param array $data
     * @param int|null $timestamp
     * @throws Exception
     */
    public function save(array $storage, array $data, int $timestamp = null): void
    {
        try {
            $datetime = DateTime::createFromFormat('U', (string)($timestamp ?? time()));
            $datetime->setTimezone(new DateTimeZone('UTC'));
            $filename = $datetime->format('Y-m-d') . ExtensionConstant::DB_EXTENSION;

            $databaseDirectoryPath = PathHelper::arrayToString([
                $this->path,
                PathHelper::arrayToStringSanitized($storage)
            ]);
            StructureHelper::createDirectoryStructure($databaseDirectoryPath);

            $databaseFilePath = PathHelper::arrayToString([$databaseDirectoryPath, $filename]);

            $valueToSave = $datetime->getTimestamp() . ':' . base64_encode(json_encode($data)) . PHP_EOL;
            file_put_contents($databaseFilePath, $valueToSave, FILE_APPEND);
        } catch (Exception $exception) {
            $this->logger->error('Exception during saving the file', [
                'exceptionClass' => get_class($exception),
                'exceptionMessage' => $exception->getMessage(),
                'exceptionTrace' => $exception->getTraceAsString()
            ]);
            throw $exception;
        }
    }

    /**
     * @param array $storagePath
     * @param int $timestamp
     * @return FileSearchResultEntity
     * @throws Exception
     */
    public function fetch(array $storagePath, int $timestamp): FileSearchResultEntity
    {
        try {
            $databaseDirectoryPath = PathHelper::arrayToString([
                $this->path,
                PathHelper::arrayToStringSanitized($storagePath)
            ]);

            $files = glob(PathHelper::arrayToString([$databaseDirectoryPath, '*' . ExtensionConstant::DB_EXTENSION]));
            if (empty($files)) {
                throw new EmptyDatabaseException('Not found `' . ExtensionConstant::DB_EXTENSION . '` files.');
            }

            $datetime = DateTime::createFromFormat('U', (string)$timestamp);
            $datetime->setTimezone(new DateTimeZone('UTC'));
            $filename = $datetime->format('Y-m-d') . ExtensionConstant::DB_EXTENSION;

            $files = FilenameHelper::clearFilenames($files);
            if (!in_array($filename, $files, true)) {
                $files[] = $filename;
            }
            sort($files);
            $files = array_values($files);

            $searchInFileQuery = new SearchInFileQuery();
            $globalResult = new FileSearchResultEntity();

            foreach (array_slice($files, array_search($filename, $files, true)) as $file) {
                $databaseFilePath = PathHelper::arrayToString([$databaseDirectoryPath, $file]);
                if (!file_exists($databaseFilePath)) {
                    continue;
                }
                $result = $searchInFileQuery->execute($databaseFilePath, $timestamp);
                if (!$globalResult->rewrite($result) || $globalResult->getExact() !== null || $globalResult->getAfter() !== null) {
                    break;
                }
            }

            if ($globalResult->getExact() !== null || $globalResult->getBefore() !== null) {
                return $globalResult;
            }

            foreach (array_reverse(array_slice($files, 0, array_search($filename, $files, true))) as $file) {
                $databaseFilePath = PathHelper::arrayToString([$databaseDirectoryPath, $file]);
                if (!file_exists($databaseFilePath)) {
                    continue;
                }
                $result = $searchInFileQuery->execute($databaseFilePath, $timestamp);
                if (!$globalResult->rewrite($result) || $globalResult->getExact() !== null || $globalResult->getBefore() !== null) {
                    break;
                }
            }

            return $globalResult;
        } catch (Exception $exception) {
            print_r($exception->getMessage());exit;
            $this->logger->error('Exception during reading the file', [
                'exceptionClass' => get_class($exception),
                'exceptionMessage' => $exception->getMessage(),
                'exceptionTrace' => $exception->getTraceAsString()
            ]);
            throw $exception;
        }
    }
}
