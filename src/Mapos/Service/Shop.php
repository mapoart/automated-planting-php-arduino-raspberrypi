<?php

namespace Mapos\Service;

use Mapos\Service\Service;
use Mapos\Service\ServiceException;
use Mapos\Storage\StorageInterface;

/**
 * Shop Service class 
 *
 * @author Marcin Polak <mapoart@gmail.com>
 */
class Shop
{

    private $productName = 'product';
    private $cartName = 'cart';

    public function __construct(StorageInterface $storage)
    {
        $this->service = Service::getInstance();
        $this->storage = $storage;
        $this->storage->selectDB(DB_NAME);
        $this->storage->selectCollection($this->cartName);
    }

    private function prepareFields($fields)
    {
        if ($user_id = gs('User_ID')) {
            return array_merge($fields, array('user_id' => new \MongoId($user_id)));
        } else {
            return array_merge($fields, array('session_id' => sid()));
        }
    }

    public function attachUser()
    {
        //We add join here user with the cart. Example at the login.
        $this->storage->selectCollection($this->cartName);
        $cartItems = $this->storage->find(array('session_id' => sid()));
        if ($cartItems->count() > 0) {
            //We update cart items to actual one (remove olad items)
            //Remove old cart
            $user_id = gs('User_ID');
            if (!$user_id) {
                sm('error:You are not logged in. Fatal Error');
                su('login.html');
            }

            $res = $this->storage->delete(array('user_id' => new \MongoId($user_id)));
            $this->storage->update(array('session_id' => sid()), array('user_id' => new \MongoId(gs('User_ID')), 'session_id' => ''));
        }
    }

    public function makeOrder($params = array())
    {
        $cartItems = $this->storage->find($this->prepareFields(array()));

        if ($cartItems->count() > 0) {
            //We found at least one product in the cart / of course this is with logged in user
            //We make order record
            $orderStorage['status'] = 'created';
            //CLIENT DETAILS: firstname, lastname, email
            $this->storage->selectTable('user');
            $clientDetails = $this->storage->findOne(array('_id' => new \MongoId(gs('User_ID'))));



            $orderStorage['user_id'] = new \MongoId(gs('User_ID'));
            $orderStorage['name'] = $clientDetails['name'];
            $orderStorage['street'] = $clientDetails['street'];
            $orderStorage['houseno'] = $clientDetails['houseno'];
            $orderStorage['postcode'] = $clientDetails['postcode'];
            $orderStorage['town'] = $clientDetails['town'];
            $orderStorage['phone'] = $clientDetails['phone'];

            if (isset($clientDetails['ni'])) {
                $orderStorage['ni'] = $clientDetails['ni'];
            }

//            $orderStorage['lastname'] = $clientDetails['lastname'];
            $orderStorage['email'] = $clientDetails['email'];
            //Total Price, Vat total, Order Date
            //Copy fields for user adresses is defined in the User model
            //Below is usage of this wonderful facility.
            $fields = gi()->get('Model', 'User')->fieldCollection('orderAddresses');

            foreach ($fields as $field):
                $orderStorage[$field] = v($params[$field]);
            endforeach;

            $orderStorage['totalPrice'] = $this->cartTotalPrice(false); // false = no VAT
            $orderStorage['totalPriceVAT'] = $this->cartTotalPrice();

            foreach ($cartItems as $cartItem):
                $newItem = &$items[];
                $newItem['id'] = $cartItem['product_id'];
                $newItem['name'] = $cartItem['name'];
                $newItem['price'] = $cartItem['price'];
                $newItem['promotion_price'] = $cartItem['promotion_price'];
                $newItem['vat'] = $cartItem['vat'];
                $newItem['qty'] = $cartItem['qty'];
                $newItem['image_1'] = $cartItem['image_1'];

                $orderStorage['items'][] = $newItem;
            endforeach;

            $this->storage->selectTable('delivery');
            $val = str_replace("'", '"', gp('delivery'));
            if (!$deliveryDetails = $this->storage->findOne(array('name' => $val))) {
                //Delivery does not exist!!!
                sm('error:Przepraszamy, ale wybrany przez Ciebie dostawca nie istnieje.');
                su('koszyk.html');
            }

            $orderStorage['delivery_id'] = $deliveryDetails['_id'];
            $orderStorage['delivery_name'] = $deliveryDetails['name'];
            $orderStorage['delivery_price'] = $deliveryDetails['price'];
            $orderStorage['delivery_vat'] = $deliveryDetails['vat'];
            $service = gi();
            $service->loadHelper('string.php');
            $orderKey = sMakeRandomString(32);

            $this->storage->selectTable('order');

            $orderStorage['date'] = now();
            $orderStorage['key'] = $orderKey;
            $orderStorage['status_id'] = '1';

            $orderStorage['totalTotal'] = $orderStorage['totalPriceVAT'] + $orderStorage['delivery_price'];

            if ($order_id = $this->storage->save($orderStorage)) {
                $this->clearCart();
                $this->sendEmails($orderKey);
                return $orderKey;
            }
            return false;
        }
    }

    function sendEmails($orderKey)
    {
        $service = gi();
        $service->loadHelper('html.php');
        $subject = o('email/order_subject', array(), true);
        $order = $this->getOrderByKey($orderKey);
        $service->db_elements = $order;
        $service->db_elements['email'] = $order['email'];

        $message = o('order_info', array(), true);
        $from = EMAIL_FROM;
        $to = $service->db_elements['email'];

        $mail = $service->get('Mail');
        $mail->setSubject($subject);

        $message = str_replace('src="', 'src="' . BASE_URL, $message);

        $mail->setMessage($message);
        $mail->setTo($to);
        $mail->setEmailFrom($from);
        return $mail->send();
    }

    function getOrderByKey($orderKey)
    {
        //TO DO: make key validation
        $this->storage->selectCollection('order');
        return $this->storage->findOne(array('key' => $orderKey));
    }

    public function atc($id, $qty)
    {//add to cart
        if ($cartExists = $this->storage->findOne($this->prepareFields(array('product_id' => new \MongoId($id))))) {
            //product exists so we add it to the database
            //later maybe add here that is 0 in the stock
            $this->storage->selectCollection($this->cartName);

            $result_qty = $cartExists['qty'] + $qty;
            if ($result_qty < 1) {
                $result_qty = 1;
            }

            $this->storage->update($cartExists['_id'], array(
                'qty' => $result_qty //We update the quantity
            ));
        } else {
            $this->storage->selectCollection($this->productName);
            $product = $this->storage->findOne(array('_id' => new \MongoId($id)));
            $this->storage->selectCollection($this->cartName);
            $this->storage->save($this->prepareFields(array(
                    'product_id' => new \MongoId($id),
                    'name' => @$product['name'],
                    'price' => @$product['price'],
                    'promotion_price' => @$product['promotion_price'],
                    'only_on_order' => @$product['only_on_order_b'],
                    'qty' => $qty,
                    'image_1' => @$product['image_1'],
                    'vat' => @$product['vat']
            )));
        }
        return $this;
    }

    public function rfc($id, $qty = null)
    {//remove from cart
        if ($cartExists = $this->storage->findOne($this->prepareFields(array('product_id' => new \MongoId($id))))) {
            //product exists so we add it to the database
            //later maybe add here that is 0 in the stock
            $this->storage->selectCollection($this->cartName);
            if ($qty) {
                $result_qty = $cartExists['qty'] - $qty;
                if ($result_qty < 1) {
                    $result_qty = 1;
                }

                $this->storage->update($cartExists['_id'], array(
                    'qty' => $result_qty//We update the quantity
                ));
            } else {
                $this->storage->delete($cartExists['_id']);
            }
        }
        return $this;
    }

    public function getCart()
    {
        $this->storage->selectCollection($this->cartName);
        $cartItems = $this->storage->find($this->prepareFields(array()));
        return isset($cartItems) ? iterator_to_array($cartItems) : array();
    }

    public function getTotalItems()
    {
        $this->storage->selectCollection($this->cartName);
        $cartItems = $this->storage->find($this->prepareFields(array()), array('qty'));
        $t = 0;
        if (!$cartItems) {
            return $t;
        }
        //TO DO:later to do one query for efficiency.
        foreach ($cartItems as $cartQty):
            $t+=$cartQty['qty'];
        endforeach;

        return $t;
    }

    public function getTotalItem($id)
    {
        $this->storage->selectCollection($this->cartName);
        $cartItems = $this->storage->find($this->prepareFields(array('product_id' => new \MongoId($id))), array('qty', 'vat', 'price', 'promotion_price'));
        $t = 0;
        if (!$cartItems) {
            return $t;
        }
        //TO DO:later to do one query for efficiency.
        foreach ($cartItems as $cartQty):
            $t+=$this->includeVat($cartQty['price'], $cartQty['vat'], $cartQty['qty'], v($cartQty['promotion_price']));
        endforeach;

        return $t;
    }

    public function cartTotalPrice($vat = true)
    {
        //Later to do calculation in one go
        $this->storage->selectCollection($this->cartName);
        $cartItems = $this->storage->find($this->prepareFields(array()), array('price', 'promotion_price', 'qty', 'vat'));
        $t = 0;
        if (!$cartItems) {
            return $t;
        }
        //TO DO:later to do one query for efficiency.
        //TO DO:later cache
        foreach ($cartItems as $cartQty):
            $price = (isset($cartQty['promotion_price']) && $cartQty['promotion_price']) ? str_replace(',', '.', $cartQty['promotion_price']) : $cartQty['price'];
            if ($vat && isset($cartQty['vat'])) {
                $t+=round($price * $cartQty['qty'] * ((100 + $cartQty['vat']) / 100), 2);
            } else {
                $t+=$price * $cartQty['qty'];
            }
        endforeach;

        return $t;
    }

    public function includeVat($price, $vat, $qty, $promotion = NULL)
    {
        $price = $promotion ? str_replace(',', '.', $promotion) : $price;
        $priceQty = $price * $qty;
        return round($priceQty * (100 + $vat) / 100, 2);
    }

    public function removeCart($id, $qty = null)
    {
        return $this->rfc($id, $qty);
    }

    public function addCart($id, $qty)
    {
        return $this->atc($id, $qty);
    }

    public function clearCart()
    {
        $this->storage->selectCollection($this->cartName);
        $this->storage->delete($this->prepareFields(array()));
    }

    public function get()
    {
        return $this; //We return this class
    }

}
