<?php

namespace Automattic\VIP\Cache;

class Vary_Cache_Test extends \WP_UnitTestCase {
	public static function setUpBeforeClass() {
		parent::setUpBeforeClass();

		require_once( __DIR__ . '/../../cache/class-vary-cache.php' );
	}

	public function setUp() {
		parent::setUp();

		$this->original_COOKIE = $_COOKIE;
	}

	public function tearDown() {
		$_COOKIE = $this->original_COOKIE;
		parent::tearDown();
	}

	/**
	 * Helper function for accessing protected methods.
	 */
	protected static function get_method( $name ) {
		$class = new \ReflectionClass( __NAMESPACE__ . '\API_Client' );
		$method = $class->getMethod( $name );
		$method->setAccessible( true );
		return $method;
	}

	public function get_test_data__is_user_in_group() {
		return [
			'group-not-defined' => [
				[],
				[],
				'dev-group',
				'yes',
				false,
			],

			'user-not-in-group' => [
				[
					'vip-go-seg' => 'design-group_--_yes',
				],
				[
					'dev-group'
				],
				'dev-group',
				'yes',
				false,
			],

			'user-in-group-and-any-segment' => [
				[
					'vip-go-seg' => 'dev-group_--_maybe',
				],
				[
					'dev-group',
				],
				'dev-group',
				null,
				true,
			],

			'user-in-group-but-different-segment' => [
				[
					'vip-go-seg' => 'dev-group_--_maybe',
				],
				[
					'dev-group',
				],
				'dev-group',
				'yes',
				false,
			],

			'user-in-group-and-same-segment' => [
				[
					'vip-go-seg' => 'dev-group_--_yes',
				],
				[
					'dev-group',
				],
				'dev-group',
				'yes',
				true,
			],
		];
	}

	/**
 	 * @dataProvider get_test_data__is_user_in_group
 	 */
	public function test__is_user_in_group( $initial_cookie, $initial_groups, $test_group, $test_value, $expected_result ) {
		$_COOKIE = $initial_cookie;
		Vary_Cache::register_groups( $initial_groups );

		$actual_result = Vary_Cache::is_user_in_group( $test_group, $test_value );

		$this->assertEquals( $expected_result, $actual_result );
	}
}
