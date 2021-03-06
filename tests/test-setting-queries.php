<?php
/**
 * WPGraphQL Test generalSetting Queries
 *
 * Test the WPGraphQL setting queries. These tests address all
 * of the default settings returned by the "get_registered_settings" method
 * in a vanilla WP core install
 *
 * @package WPGraphQL
 *
 */
class WP_GraphQL_Test_Setting_Queries extends WP_UnitTestCase {

	/**
	 * This function is run before each method
	 *
	 * @access public
	 * @return void
	 */
	public function setUp() {

		parent::setUp();

		$this->admin = $this->factory->user->create( [
			'role' => 'administrator',
		] );

		$this->editor = $this->factory->user->create( [
			'role' => 'editor',
		] );
	}

	/**
	 * This function is run after each method
	 *
	 * @access public
	 * @return void
	 */
	public function tearDown() {
		parent::tearDown();
	}

	/**
	 * Method for testing whether a user can query settings
	 * if they don't have the 'manage_options' capability
	 *
	 * @access public
	 * @return void
	 */
	public function testSettingQueryAsEditor() {
		/**
		 * Set the editor user
		 * Set the query
		 * Make the request
		 * Validate the request has errors
		 */
		wp_set_current_user( $this->editor );
		$query = "
			query {
				generalSettings {
				    email
			    }
		    }
	    ";
		$actual = do_graphql_request( $query );

		$this->assertArrayHasKey( 'errors', $actual );

	}

	/**
	 * Method for testing the generalSettings
	 *
	 * @access public
	 * @return void
	 */
	public function testGeneralSettingQuery() {
		/**
		 * Set the admin user
		 * Set the query
		 * Make the request
		 * Validate the request
		 */
		wp_set_current_user( $this->admin );

		$mock_options = [
			'date_format' => 'test date format',
			'blogdescription' => 'test description',
			'admin_email' => 'test@test.com',
			'language' => 'test language',
			'start_of_week' => 0,
			'time_format' => 'test_time_format',
			'timezone_string' => 'UTC',
			'blogname' => 'test_title',
			'siteurl' => 'http://test.com'
		];

		foreach ( $mock_options as $mock_option_key => $mock_value ) {
			update_option( $mock_option_key, $mock_value );
		}

		if ( is_multisite() ) {
			update_network_option( 1, 'admin_email', 'test email' );
		}

		if ( true === is_multisite() ) {
			$query = "
				query {
					generalSettings {
					    dateFormat
					    description
					    language
					    startOfWeek
					    timeFormat
					    timezone
					    title
					}
				}
			";
		} else {
			$query = "
				query {
					generalSettings {
					    dateFormat
					    description
					    email
					    language
					    startOfWeek
					    timeFormat
					    timezone
					    title
					    url
					}
				}
			";
		}

		$actual = do_graphql_request( $query );

		$generalSettings = $actual['data']['generalSettings'];


		$this->assertNotEmpty( $generalSettings );
		$this->assertEquals( $mock_options['date_format'], $generalSettings['dateFormat'] );
		$this->assertEquals( $mock_options['blogdescription'], $generalSettings['description'] );
		if ( ! is_multisite() ) {
			$this->assertEquals( $mock_options['admin_email'], $generalSettings['email'] );
		}
		$this->assertEquals( $mock_options['start_of_week'], $generalSettings['startOfWeek'] );
		$this->assertEquals( $mock_options['time_format'], $generalSettings['timeFormat'] );
		$this->assertEquals( $mock_options['timezone_string'], $generalSettings['timezone'] );
		$this->assertEquals( $mock_options['blogname'], $generalSettings['title'] );
		if ( ! is_multisite() ) {
			$this->assertEquals( $mock_options['siteurl'], $generalSettings['url'] );
		}
	}

	/**
	 * Method for testing the writingSettings
	 *
	 * @access public
	 * @return void
	 */
	public function testWritingSettingQuery() {
		/**
		 * Set the admin user
		 * Set the query
		 * Make the request
		 * Validate the request
		 */
		wp_set_current_user( $this->admin );
		$query = "
			query {
				writingSettings {
				    defaultCategory
				    defaultPostFormat
				    useSmilies
				}
			}
		";
		$actual = do_graphql_request( $query );

		$writingSettings = $actual['data']['writingSettings'];

		$this->assertNotEmpty( $writingSettings );
		$this->assertTrue( is_int( $writingSettings['defaultCategory'] ) );
		$this->assertTrue( is_string( $writingSettings['defaultPostFormat'] ) );
		$this->assertTrue( is_bool( $writingSettings['useSmilies'] ) );

	}

	/**
	 * Method for testing the readingSettings
	 *
	 * @access public
	 * @return array $actual
	 */
	public function testReadingSettingQuery() {
		/**
		 * Set the admin user
		 * Set the query
		 * Make the request
		 * Validate the request
		 */
		wp_set_current_user( $this->admin );

		update_option( 'posts_per_page', 12 );

		$query = "
			query {
				readingSettings {
				    postsPerPage
				}
			}
		";
		$actual = do_graphql_request( $query );

		$readingSettings = $actual['data']['readingSettings'];

		$this->assertNotEmpty( $readingSettings );
		$this->assertEquals( 12, $readingSettings['postsPerPage'] );

	}

	/**
	 * Method for testing the discussionSettings
	 *
	 * @access public
	 * @return array $actual
	 */
	public function testDiscussionSettingQuery() {
		/**
		 * Set the admin user
		 * Set the query
		 * Make the request
		 * Validate the request
		 */
		wp_set_current_user( $this->admin );

		update_option( 'default_comment_status', 'test_value' );
		update_option( 'default_ping_status', 'test_value' );

		$query = "
			query {
				discussionSettings {
				    defaultCommentStatus
				    defaultPingStatus
				}
			}
		";
		$actual = do_graphql_request( $query );

		$discussionSettings = $actual['data']['discussionSettings'];

		$this->assertNotEmpty( $discussionSettings );
		$this->assertEquals( 'test_value', $discussionSettings['defaultCommentStatus'] );
		$this->assertEquals( 'test_value', $discussionSettings['defaultPingStatus'] );

	}

}
