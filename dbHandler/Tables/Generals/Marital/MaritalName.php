<?php
/**
 * @PHP       Version >= 8.0
 * @Liberary  AdminPortalHandler
 * @Project   AdminPortalHandler
 * @copyright ©2024 Maatify.dev
 * @author    Mohamed Abdulalim (megyptm) <mohamed@maatify.dev>
 * @since     2025-01-13 7:59 AM
 * @link      https://www.maatify.dev Maatify.com
 * @link      https://github.com/Maatify/AdminPortalHandler  view project on GitHub
 * @Maatify   AdminPortalHandler :: MaritalName
 */

namespace Maatify\Portal\Generals\Marital;

use App\DB\DBS\DbConnector;
use Maatify\Portal\Language\DbLanguage;

class MaritalName extends DbConnector
{
    public const TABLE_NAME                 = 'marital_name';
    public const TABLE_ALIAS                = 'marital';
    public const IDENTIFY_TABLE_ID_COL_NAME = Marital::IDENTIFY_TABLE_ID_COL_NAME;
    public const LOGGER_TYPE                = self::TABLE_NAME;
    public const LOGGER_SUB_TYPE            = 'name';
    public const COLS                       =
        [
            self::IDENTIFY_TABLE_ID_COL_NAME       => 1,
            DbLanguage::IDENTIFY_TABLE_ID_COL_NAME => 1,
            'name'                                 => 0,
        ];
    public const IMAGE_FOLDER               = self::TABLE_NAME;

    protected string $tableName = self::TABLE_NAME;
    protected string $tableAlias = self::TABLE_ALIAS;
    protected string $identify_table_id_col_name = self::IDENTIFY_TABLE_ID_COL_NAME;
    protected string $logger_sub_type = self::LOGGER_SUB_TYPE;
    protected array $cols = self::COLS;
    protected string $image_folder = self::IMAGE_FOLDER;

    private static self $instance;

    public static function obj(): self
    {
        if (empty(self::$instance)) {
            self::$instance = new self();
        }

        return self::$instance;
    }
}