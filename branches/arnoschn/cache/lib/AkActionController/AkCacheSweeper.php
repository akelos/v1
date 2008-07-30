<?php
require_once(AK_LIB_DIR . DS . 'AkActiveRecord' . DS . 'AkObserver.php');
/**
 * @package ActionController
 * @subpackage Caching
 * @author Arno Schneider
 * @license GNU Lesser General Public License <http://www.gnu.org/copyleft/lesser.html>
 */

/**
 * Cache Sweepers need to be stored under:
 * 
 * AK_BASE_DIR/app/sweepers
 * 
 * Sweepers are the terminators of the caching world and responsible for expiring caches when model objects change.
 * They do this by being half-observers, half-filters and implementing callbacks for both roles. A Sweeper example:
 *
 *   class ListSweeper extends AkCacheSweeper
 *     var $observe = array("List", "Item");
 *
 *     function afterSave(&$record) {
 *         $list = is_a($record,"List") ? $record : $record->list;
 *         $this->expirePage(array("controller" => "lists", "action" => "public", "id" => $list->id));
 *         $this->expireAction(array("controller" => "lists", "action" => "all"));
 *         foreach($list->shares as $share) {
 *             $this->expirePage(array("controller" => "lists", "action" => "show", "id" => $share->id));
 *         }
 *     }
 *   }
 *
 * The sweeper is assigned in the controllers that wish to have its job performed using the <tt>cache_sweeper</tt> class method:
 *
 *   class ListsController extends ApplicationController {
 *     var $caches_action = array("index", "show", "public", "feed");
 *     var $cache_sweeper = array("list_sweeper", "only" => array("edit", "destroy", "share"));
 *     ....
 *   }
 *
 * In the example above, four actions are cached and three actions are responsible for expiring those caches.
 */
class AkCacheSweeper extends AkObserver
{
    var $_cache_handler;

    function __construct(&$cache_handler)
    {
        $this->_cache_handler = $cache_handler;
        parent::__construct();
    }
    function expirePage($path = null, $language=null)
    {
        return $this->_cache_handler->expirePage($path,$language);
    }
    function expireAction($options, $params = array())
    {
        return $this->_cache_handler->expireAction($options, $params);
    }
    function expireFragment($key, $options = array())
    {
        return $this->_cache_handler->expireFragment($key, $options);
    }


}