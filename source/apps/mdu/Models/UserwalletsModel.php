<?php 
namespace Ucenter\Mdu\Models;

/**
* UserWalletModel
*/
class UserwalletsModel extends ModelBase
{
    public function createWallet($uid)
    {
        $data = array(
                'u_id' => $uid
        );
        if ($this->db->execute('INSERT INTO cloud_user_wallets(u_id) VALUES(:u_id)', $data)) {
            return 1;
        } else {
            return 0;
        }
    }

    public function coinsInfo($uid)
    {
        return $this->db->query('SELECT uw_coins, uw_all_coins FROM cloud_user_wallets WHERE u_id = ? limit 1', array($uid))->fetch();
    }

    public function receive($uid, $coins)
    {
        return $this->db->execute("UPDATE cloud_user_wallets SET uw_coins = uw_coins + $coins, uw_all_coins = uw_all_coins + $coins WHERE u_id = $uid AND uw_status = 1");
    }
}
?>