<?php
namespace Headzoo\CoinTalk;

/**
 * Manages a pool of Server instances
 *
 * Acts as an instance of IServer, which transparently uses a pool of IServer
 * instances to query wallets.
 *
 * Example:
 * <code>
 *  $pool = new Pool();
 *  $conf = [
 *      "user" => "test",
 *      "pass" => "pass",
 *      "host" => "localhost",
 *      "port" => 9332
 *  ];
 *  $server = new Server($conf);
 *  $pool->add($server);
 *
 *  $conf = [
 *      "user" => "test",
 *      "pass" => "pass",
 *      "host" => "localhost",
 *      "port" => 9333
 *  ];
 *  $server = new Server($conf);
 *  $pool->add($server);
 *
 *  $info = $pool->query("getinfo");
 */
class Pool
    implements IServer
{
    /**
     * The IServer instances in the pool
     * @var IServer[]
     */
    private $servers = [];

    /**
     * Number of servers in the pool
     * @var int
     */
    private $count = 0;

    /**
     * Index of the last server returned from the pool
     * @var int
     */
    private $index = 0;

    /**
     * Adds an IServer instance to the pool
     *
     * @param IServer $server The Server instance
     * @return $this
     */
    public function add(IServer $server)
    {
        $this->servers[] = $server;
        $this->count++;
        return $this;
    }

    /**
     * Returns an IServer instance from the pool
     *
     * Returns null when there are no servers in the pool.
     *
     * @return IServer|null
     */
    public function get()
    {
        $server = null;
        if ($this->count > 0) {
            $server = $this->servers[$this->index];
            if (++$this->index > $this->count - 1) {
                $this->index = 0;
            }
        }

        return $server;
    }

    /**
     * Returns the number of Server instances in the pool
     *
     * @return int
     */
    public function count()
    {
        return $this->count;
    }

    /**
     * {@inheritDoc}
     *
     * @throws ServerException When the pool has no available servers
     */
    public function query($method, $params = [])
    {
        $server = $this->get();
        if (null === $server) {
            throw new ServerException(
                "No Server instances available in the pool."
            );
        }

        return $server->query($method, $params);
    }
}