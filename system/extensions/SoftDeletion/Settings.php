<?php
/**
 * Settings for the Soft Deletion Extension
 * @category Extensions
 * @package  Extensions
 * @subpackage SoftDeletion
 * @author     Rachel Higley <me@rachelhigley.com>
 * @copyright  2013 Framework Simple
 * @license    http://www.opensource.org/licenses/mit-license.php MIT
 * @link       http://rachelhigley.com/framework
 */

/**
 * deletion_col_name: strng
 *
 * the name of the column to check on
 */
CONST DELETION_COL_NAME = "date_deleted";

/**
 * deletion_type
 *
 *  the type of value to be inserted
 *  - timestamp = current timestamp
 *  - boolean = 1
 */
CONST DELETION_TYPE = "timestamp";