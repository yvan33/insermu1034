<?php

/**
 * A model to manage database changes
 *
 * @author     Justas Butkus <justas@butkus.lt>
 * @since      2013.01.23
 *
 * @package    AllInOneCalendar
 * @subpackage AllInOneCalendar.Model.Meta
 */
class Ai1ec_Database_Applicator
{

	/**
	 * @staticvar Ai1ec_Database_Applicator Singletonian instance of self
	 */
	static protected $_instance = NULL;

	/**
	 * @var wpdb Instance of wpdb object
	 */
	protected $_db = NULL;

	/**
	 * @var Ai1ec_Database Instance of Ai1ec_Database object
	 */
	protected $_database = NULL;

	/**
	 * get_instance method
	 *
	 * Get singleton instance of self (Ai1ec_Database_Applicator).
	 *
	 * @return Ai1ec_Database_Applicator Initialized instance of self
	 */
	static public function get_instance() {
		if ( ! ( self::$_instance instanceof self ) ) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}

	/**
	 * remove_instance_duplicates method
	 *
	 * Remove duplicate instances, from `event_instances` table
	 *
	 * @param int $depth Private argument, denoting number of iterations to
	 *                   try, before reverting to slow approach
	 *
	 * @return bool Success
	 */
	public function remove_instance_duplicates( $depth = 5 ) {
		$use_field  = 'id';
		if ( $depth < 0 ) {
			$use_field = 'post_id';
		}
		$table      = $this->_table( 'event_instances' );
		if ( false === $this->_database->table_exists( $table ) ) {
			return true;
		}
		$duplicates = $this->find_duplicates(
			$table,
			$use_field,
			array( 'post_id', 'start' )
		);
		$count      = count( $duplicates );
		if ( $count > 0 ) {
			$sql_query  = 'DELETE FROM ' . $table .
				' WHERE ' . $use_field . ' IN ( ' .
				implode( ', ', $duplicates ) . ' )';
			$this->_db->query( $sql_query );
		}
		if ( 'post_id' === $use_field ) { // slow branch
			global $ai1ec_events_helper;
			foreach ( $duplicates as $post_id ) {
				try {
					$event = new Ai1ec_Event( $post_id );
					$ai1ec_events_helper->cache_event( $event );
				} catch ( Exception $excpt ) {
					// discard any errors
				}
			}
		} elseif ( $count > 0 ) { // retry
			return $this->remove_instance_duplicates( --$depth );
		}
		return true;
	}

	/**
	 * find_duplicates method
	 *
	 * Find a list of duplicates in table, given search key and groupping fields
	 *
	 * @param string $table   Name of table, to search duplicates in
	 * @param string $primary Column, to return values for
	 * @param array  $group   List of fields, to group values on
	 *
	 * @return array List of primary field values
	 */
	public function find_duplicates( $table, $primary, array $group ) {
		$sql_query = '
			SELECT
				MIN( {{primary}} ) AS dup_primary -- pop oldest
			FROM {{table}}
			GROUP BY {{group}}
			HAVING COUNT( {{primary}} ) > 1
		';
		$sql_query = str_replace(
			array(
				'{{table}}',
				'{{primary}}',
				'{{group}}',
			),
			array(
				$this->_table( $table ),
				$this->_escape_column( $primary ),
				implode(
					', ',
					array_map( array( $this, '_escape_column' ), $group )
				),
			),
			$sql_query
		);
		$result = $this->_db->get_col( $sql_query );
		return $result;
	}

	/**
	 * _table method
	 *
	 * Get fully qualified table name, to use in queries
	 *
	 * @param string $table Name of table, to convert
	 *
	 * @return string Qualified table name
	 */
	protected function _table( $table ) {
		$prefix = $this->_db->prefix . 'ai1ec_';
		if ( substr( $table, 0, strlen( $prefix ) ) !== $prefix ) {
			$table = $prefix . $table;
		}
		return $table;
	}

	/**
	 * _escape_column method
	 *
	 * Escape column, enquoting it in MySQL specific characters
	 *
	 * @param string $name Name of column to quote
	 *
	 * @return string Escaped column name
	 */
	protected function _escape_column( $name ) {
		return '`' . $name . '`';
	}

	/**
	 * Constructor
	 *
	 * Initialize object, by storing instance of `wpdb` in local variable
	 *
	 * @return void Constructor does not return
	 */
	protected function __construct() {
		global $wpdb;
		$this->_db = $wpdb;
		$this->_database = Ai1ec_Database::instance();
	}

}
