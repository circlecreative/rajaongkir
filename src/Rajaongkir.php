<?php
/**
 * Rajaongkir
 *
 * RajaOngkir API PHP Class
 *
 * This content is released under GNU General Public License v2.0 License
 *
 * Copyright (c) 2015, Steeve Andrian
 *
 * @package        Applications
 * @author         Steeve Andrian
 * @copyright      Copyright (c) 2015, Steeve Andrian.
 * @filesource
 */

// ------------------------------------------------------------------------

namespace Steevenz;

// ------------------------------------------------------------------------

use O2System\CURL;
use O2System\CURL\Interfaces\Method;

/**
 * Rajaongkir
 *
 * @package       application
 * @subpackage    libraries
 * @category      library class
 *
 * @version       1.0.0
 * @author        Steeven Andrian Salim
 */
class Rajaongkir
{
    /**
     * Rajaongkir Account Type
     *
     * @access  protected
     * @type    string
     */
    protected $_account_type = 'starter';

    /**
     * API Key
     *
     * @access  protected
     * @type    string
     */
    protected $_api_key = NULL;

    /**
     * API URL
     *
     * @access  protected
     * @type    string
     */
    protected $_api_url = NULL;

    /**
     * List of API URLs Endpoint
     *
     * @access  protected
     * @type    array
     */
    protected $_api_urls = array(
        'starter' => 'http://rajaongkir.com/api/starter/',
        'basic'   => 'http://rajaongkir.com/api/basic/',
        'pro'     => 'http://pro.rajaongkir.com/api/'
    );

    /**
     * O2System CURL Resource
     *
     * @access  protected
     * @type    \O2System\CURL
     */
    protected $_curl;

    /**
     * Class Constructor
     *
     * @access  public
     * @throws  \InvalidArgumentException
     */
    public function __construct( $api_key = NULL, $account_type = NULL )
    {
        if( isset( $api_key ) )
        {
            if( is_array( $api_key ) )
            {
                if( isset( $api_key[ 'api_key' ] ) )
                {
                    $this->_api_key = $api_key[ 'api_key' ];
                }

                if( isset( $api_key[ 'account_type' ] ) )
                {
                    $this->_account_type = $api_key[ 'account_type' ];
                }
                elseif( ! empty( $this->_api_key ) AND ! isset( $this->_account_type ) )
                {
                    $this->_account_type = 'basic';
                }
            }
            elseif( is_string( $api_key ) )
            {
                $this->_api_key = $api_key[ 'api_key' ];
            }
        }

        if( isset( $account_type ) )
        {
            $this->_account_type = $account_type;
        }
        elseif( ! empty( $this->_api_key ) AND ! isset( $this->_account_type ) )
        {
            $this->_account_type = 'basic';
        }

        if( array_key_exists( $this->_account_type, $this->_api_urls ) )
        {
            $this->_api_url = $this->_api_urls[ $this->_account_type ];
        }
        else
        {
            throw new \InvalidArgumentException( 'Rajaongkir: Invalid Account Type' );
        }

        /*
         * ------------------------------------------------------
         *  Initialized O2System CURL Class
         * ------------------------------------------------------
         */
        $this->_curl = new CURL();
    }

    // ------------------------------------------------------------------------

    /**
     * Set API Key
     *
     * @param   string $api_key RajaOngkir API Key
     *
     * @access  public
     * @return  object
     */
    public function set_api_key( $api_key )
    {
        $this->_api_key = $api_key;

        return $this;
    }

    // ------------------------------------------------------------------------

    /**
     * Set Account Type
     *
     * @param   string $account_type RajaOngkir Account Type, can be starter, basic or pro
     *
     * @access  public
     * @return  object
     * @throws  \InvalidArgumentException
     */
    public function set_account_type( $account_type )
    {
        if( array_key_exists( $account_type, $this->_api_urls ) )
        {
            $this->_account_type = $account_type;
            $this->_api_url = $this->_api_urls[ $this->_account_type ];
        }
        else
        {
            throw new \InvalidArgumentException( 'Rajaongkir: Invalid Account Type' );
        }

        return $this;
    }

    // ------------------------------------------------------------------------

    /**
     * API Request
     *
     * @param string $path
     * @param array  $params
     * @param string $type
     *
     * @access  protected
     * @return  mixed
     */
    protected function _request( $path, $params = array(), $type = 'GET' )
    {
        $headers = array();

        if( $this->_account_type !== 'starter' )
        {
            $headers[ 'key' ] = $this->_api_key;
        }

        switch( $type )
        {
            default:
            case 'GET':
                $response = $this->_curl->get( $this->_api_url, $path, $params, $headers );
                break;

            case 'POST':
                $headers[ 'content-type' ] = 'application/x-www-form-urlencoded';
                $response = $this->_curl->post( $this->_api_url, $path, $params, $headers );
                break;
        }

        if( $response->meta->http_code === 200 )
        {
            if( isset( $response->body->rajaongkir->results ) )
            {
                return $response->body->rajaongkir->results;
            }
        }
        elseif( $response->meta->http_code === 400 )
        {
            return $response->body->rajaongkir->status->description;
        }

        return FALSE;
    }

    // ------------------------------------------------------------------------

    /**
     * Get Provinces
     *
     * @access  public
     * @return  mixed
     */
    public function get_provinces()
    {
        return $this->_request( 'province' );
    }

    // ------------------------------------------------------------------------

    /**
     * Get Provinces
     *
     * @param   int $id_province Province ID
     *
     * @access  public
     * @return  mixed
     */
    public function get_province( $id_province )
    {
        return $this->_request( 'province', [ 'id' => $id_province ] );
    }

    // ------------------------------------------------------------------------

    /**
     * Get Cities
     *
     * @param   int $id_province Province ID
     *
     * @access  public
     * @return  mixed
     */
    public function get_cities( $id_province = NULL )
    {
        $params = array();

        if( ! is_null( $id_province ) )
        {
            $params[ 'province' ] = $id_province;
        }

        return $this->_request( 'city', $params );
    }

    // ------------------------------------------------------------------------

    /**
     * Get City
     *
     * @param   int $id_city City ID
     *
     * @access  public
     * @return  mixed
     */
    public function get_city( $id_city )
    {
        return $this->_request( 'city', [ 'id' => $id_city ] );
    }

    // ------------------------------------------------------------------------

    /**
     * Get Subdisctricts
     *
     * @param   int $id_city City ID
     *
     * @access  public
     * @return  mixed
     */
    public function get_subdisctricts( $id_city )
    {
        return $this->_request( 'subdistrict', [ 'city' => $id_city ] );
    }

    // ------------------------------------------------------------------------

    /**
     * Get Subdisctrict
     *
     * @param   int $id_subdistrict Subdistrict ID
     *
     * @access  public
     * @return  mixed
     */
    public function get_subdisctrict( $id_subdistrict )
    {
        return $this->_request( 'subdistrict', [ 'id' => $id_subdistrict ] );
    }

    // ------------------------------------------------------------------------

    /**
     * Get International Origins
     *
     * @param   int $id_province Province ID
     *
     * @access  public
     * @return  mixed
     */
    public function get_international_origins( $id_province = NULL )
    {
        if( $this->_account_type === 'starter' ) return FALSE;

        $params = array();

        if( isset( $id_province ) )
        {
            $params[ 'province' ] = $id_province;
        }

        return $this->_request( 'internationalOrigin', $params );
    }

    // ------------------------------------------------------------------------

    /**
     * Get International Origin
     *
     * @param   int $id_city City ID
     *
     * @access  public
     * @return  mixed
     */
    public function get_international_origin( $id_city = NULL )
    {
        if( $this->_account_type === 'starter' ) return FALSE;

        return $this->_request( 'internationalOrigin', [ 'id' => $id_city ] );
    }

    // ------------------------------------------------------------------------

    /**
     * Get International Destinations
     *
     * @param   int $id_country Country ID
     *
     * @access  public
     * @return  mixed
     */
    public function get_international_destinations()
    {
        if( $this->_account_type === 'starter' ) return FALSE;

        return $this->_request( 'internationalDestination' );
    }

    /**
     * Get International Destination
     *
     * @param   int $id_country Country ID
     *
     * @access  public
     * @return  mixed
     */
    public function get_international_destination( $id_country )
    {
        if( $this->_account_type === 'starter' ) return FALSE;

        $params = array();

        if( isset( $id_country ) )
        {
            $params[ 'id' ] = $id_country;
        }

        return $this->_request( 'internationalDestination', $params );
    }

    /**
     * Get Cost
     *
     * @example
     * $rajaongkir->get_cost(
     *      ['city' => 1],
     *      ['subdistrict' => 12],
     *      ['weight' => 100, 'length' => 100, 'width' => 100, 'height' => 100, 'diameter' => 100],
     *      'jne'
     * );
     *
     * @see      http://rajaongkir.com/dokumentasi/pro
     *
     * @param array  $origin            City, District or Subdistrict Origin
     * @param array  $destination       City, District or Subdistrict Destination
     * @param array  $metrics           Array of Specification
     *                                  weight      int     weight in gram (required)
     *                                  length      number  package length dimension
     *                                  width       number  package width dimension
     *                                  height      number  package height dimension
     *                                  diameter    number  package diameter
     * @param string $courier           Courier Code
     *
     * @access   public
     * @return  mixed
     */
    public function get_cost( array $origin, array $destination, $metrics, $courier )
    {
        if( key( $destination ) === 'country' AND $this->_account_type === 'starter' ) return FALSE;

        $params[ 'origin' ] = $origin[ key( $origin ) ];
        $params[ 'destination' ] = $destination[ key( $destination ) ];
        $params[ 'courier' ] = $courier;

        if( $this->_account_type === 'pro' AND key( $destination ) !== 'country' )
        {
            $params[ 'originType' ] = key( $origin );
            $params[ 'destinationType' ] = key( $destination );
        }

        if( is_array( $metrics ) )
        {
            if( ! isset( $metrics[ 'weight' ] ) AND
                isset( $metrics[ 'length' ] ) AND
                isset( $metrics[ 'width' ] ) AND
                isset( $metrics[ 'height' ] )
            )
            {
                $metrics[ 'weight' ] = ( ( $metrics[ 'length' ] * $metrics[ 'width' ] * $metrics[ 'height' ] ) / 6000 ) * 1000;
            }
            elseif( isset( $metrics[ 'weight' ] ) AND
                    isset( $metrics[ 'length' ] ) AND
                    isset( $metrics[ 'width' ] ) AND
                    isset( $metrics[ 'height' ] )
            )
            {
                $weight = ( ( $metrics[ 'length' ] * $metrics[ 'width' ] * $metrics[ 'height' ] ) / 6000 ) * 1000;

                if( $weight > $metrics[ 'weight' ] )
                {
                    $metrics[ 'weight' ] = $weight;
                }
            }

            foreach( $metrics as $key => $value )
            {
                $params[ $key ] = $value;
            }
        }
        else
        {
            $params[ 'weight' ] = $metrics;
        }

        $path = key( $destination ) === 'country' ? 'internationalCost' : 'cost';

        return $this->_request( $path, $params, Method::POST );
    }

    // ------------------------------------------------------------------------

    /**
     * Get Waybill
     *
     * @param   int         $id_waybill Receipt ID
     * @param   null|string $courier    Courier Code
     *
     * @access  public
     * @return  mixed
     */
    public function get_waybill( $id_waybill, $courier )
    {
        if( $this->_account_type !== 'pro' ) return FALSE;

        return $this->_request( 'waybill', array(
            'key'     => $this->_api_key,
            'waybill' => $id_waybill,
            'courier' => $courier
        ), Method::POST );
    }
}