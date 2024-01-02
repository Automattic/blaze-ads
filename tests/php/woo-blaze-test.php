<?php
/**
 * Class Woo_Blaze_Test
 *
 * @package WooCommerce\Blaze\Tests
 */
class Woo_Blaze_Test extends WB_Unit_Test_Case {

	public function set_up() {
		parent::set_up();

		wp_set_current_user(
			self::factory()->user->create(
				array( 'role' => 'administrator' )
			)
		);
	}

	public function test_it_runs_adds_admin_menu_at_priority_10() {
		$install_actions_priority = has_action(
			'admin_menu',
			array( Woo_Blaze::class, 'add_admin_menu' )
		);

		$this->assertEquals( 10, $install_actions_priority );
	}

	public function test_it_adds_admin_menu_correctly() {
		Woo_Blaze::add_admin_menu();

		$menu_url = menu_page_url( 'woo-blaze' );
		$this->assertNotEmpty( $menu_url );
		$this->assertMatchesRegularExpression( '/woocommerce-marketing/', $menu_url );
	}
}
