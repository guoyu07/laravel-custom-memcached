<?php namespace Xjtuwangke\LaravelCustomMemcached;

use Illuminate\Support\ServiceProvider;

use Illuminate\Cache\Repository;
use Illuminate\Session\CacheBasedSessionHandler;

class LaravelCustomMemcachedServiceProvider extends ServiceProvider {

	/**
	 * Indicates if loading of the provider is deferred.
	 *
	 * @var bool
	 */
	protected $defer = false;

	/**
	 * Bootstrap the application events.
	 *
	 * @return void
	 */
	public function boot()
	{
		$this->package('xjtuwangke/laravel-custom-memcached');

		\Cache::extend( 'custom-memcached' , function( $app ){
			$memcached = $app['config']['memcached.instance'];
			if( is_callable( $memcached ) ){
				$memcached = $memcached();
			}
			else{
				throw new \RuntimeException("Could not find memcached instance closure in config memcached.instance.");
			}
			return new Repository( new \Illuminate\Cache\MemcachedStore( $memcached , $app['config']['cache.prefix'] ) );
		});

	}

	/**
	 * Register the service provider.
	 *
	 * @return void
	 */
	public function register()
	{
		//
		// register a Custom Session SP at startup
		\Session::extend( 'custom-memcached' , function( $app ){
			$minutes = $this->app['config']['session.lifetime'];
			$memcached = $app['config']['memcached.instance'];
			if( is_callable( $memcached ) ){
				$memcached = $memcached();
			}
			else{
				throw new \RuntimeException("Could not find memcached instance closure in config memcached.instance.");
			}
			$repo = new Repository( new \Illuminate\Cache\MemcachedStore( $memcached , $app['config']['cache.prefix'] ) );
			return new CacheBasedSessionHandler( $repo , $minutes );
		});
	}

	/**
	 * Get the services provided by the provider.
	 *
	 * @return array
	 */
	public function provides()
	{
		return array();
	}

}
