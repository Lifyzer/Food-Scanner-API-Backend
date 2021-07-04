<?php
/**
 * @author         Pierre-Henry Soria <hello@lifyzer.com>
 * @copyright      (c) 2018, Pierre-Henry Soria. All Rights Reserved.
 * @license        GNU General Public License; <https://www.gnu.org/licenses/gpl-3.0.en.html>
 */

declare(strict_types=1);

namespace Lifyzer\Api;

use PDO;

class Database extends PDO
{
    private const DSN_MYSQL_PREFIX = 'mysql';
    private const DSN_POSTGRESQL_PREFIX = 'pgsql';
    private const DBMS_CHARSET = 'UTF8MB4';

    public function __construct()
    {
        $details = $this->getDetails();
        $driverOptions[PDO::MYSQL_ATTR_INIT_COMMAND] = "SET NAMES {$details['charset']}";
        parent::__construct(
            "{$details['db_type']}:host={$details['host']};dbname={$details['name']};",
            $details['user'],
            $details['password'],
            $driverOptions
        );
        $this->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }

    private function getDetails(): array
    {
        return [
            'db_type' => self::DSN_MYSQL_PREFIX,
            'host' => getenv('DB_HOST'),
            'name' => getenv('DB_NAME'),
            'user' => getenv('DB_USER'),
            'password' => getenv('DB_PWD'),
            'charset' => self::DBMS_CHARSET
        ];
    }
}
