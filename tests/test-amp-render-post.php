<?php

class AMP_Render_Post_Test extends WP_UnitTestCase {
	public function test__invalid_post() {
		// No ob here since it bails early
		$amp_rendered = amp_render_post( PHP_INT_MAX );

		$this->assertNull( $amp_rendered, 'Response was not null' );
		$this->assertEquals( 0, did_action( 'pre_amp_render_post' ), 'pre_amp_render_post action fire when it should not have.' );
	}

	public function test__valid_post() {
		$user_id = $this->factory->user->create();
		$post_id = $this->factory->post->create( array( 'post_author' => $user_id ) );

		// Need to use ob here since the method echos
		ob_start();
		amp_render_post( $post_id );
		$amp_rendered = ob_get_clean();

		$this->assertContains( '<html amp', $amp_rendered, 'Response does not include html tag with amp attribute.' );
		$this->assertEquals( 1, did_action( 'pre_amp_render_post', 'pre_amp_render_post action fire either did not fire or fired too many times.' ) );
	}

	/**
	 * Stored result of is_amp_endpoint() when calling amp_render_post().
	 *
	 * @var bool
	 */
	protected $was_amp_endpoint;

	/**
	 * Test is_amp_endpoint.
	 *
	 * @covers is_amp_endpoint()
	 */
	public function test__is_amp_endpoint() {
		$user_id = $this->factory->user->create();
		$post_id = $this->factory->post->create( array(
			'post_author' => $user_id,
		) );

		$before_is_amp_endpoint = is_amp_endpoint();

		add_action( 'pre_amp_render_post', array( $this, 'check_is_amp_endpoint' ) );
		$this->was_amp_endpoint = false;

		// Need to use output buffering here since the method echos.
		ob_start();
		amp_render_post( $post_id );
		$amp_rendered = ob_get_clean();
		$this->assertContains( '<html amp', $amp_rendered );

		$after_is_amp_endpoint = is_amp_endpoint();

		$this->assertFalse( $before_is_amp_endpoint, 'is_amp_endpoint was not defaulting to false before amp_render_post' );
		$this->assertTrue( $this->was_amp_endpoint, 'is_amp_endpoint was not forced to true during amp_render_post' );
		$this->assertFalse( $after_is_amp_endpoint, 'is_amp_endpoint was not reset after amp_render_post' );
	}

	/**
	 * Store whether it currently is_amp_endpoint().
	 */
	public function check_is_amp_endpoint() {
		$this->was_amp_endpoint = is_amp_endpoint();
	}
}
