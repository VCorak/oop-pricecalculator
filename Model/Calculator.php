<?php

declare(strict_types=1);

class Calculator
{

    // DECLARING THE PROPERTIES

    // PRODUCT
    private int $idProduct;
    private float $price;

    // CUSTOMER
    private int $idCustomer;
    private int $customerFixed;
    private int $customerVariable;

    // GROUP
    private int $sumFixedGroupDisc;
    private array $groupVariable;
    private array $groupFixed;
    private int $maxVarGroupDisc;
    private string $bestGroupDisc;

    public function __construct(int $idCustomer, int $idProduct)
    {
        $this->idCustomer = $idCustomer;
        $this->idProduct = $idProduct;
    }

    // METHODS

    public function getDisc()
    {
        // Checking if the customer ID is set
        if (isset($this->idCustomer)) {

            // LOADING CUSTOMER DATA
            $loaderCustomer = new CustomerLoader();
            $allCustomers = $loaderCustomer->getAllCustomers();

            // WE NEED CUSTOMER ID IN ORDER TO AVOID DOUBLE NAMES (COULD GIVE US WRONG RESULTS)
            $customer = $loaderCustomer->getCustomerById($this->idCustomer);

            // LOADING GROUP DATA
            $loaderCustomerGroup = new CustomerGroupLoader();
            $allCustomerGroups = $loaderCustomerGroup->getAllCustomerGroups();

            // LOADING THE GROUP ID AND ITS DISCOUNTS
            $customerGroup = $customer->getGroupId();
            $this->customerFixed = $customer->getFixedDiscount();
            $this->customerVariable = $customer->getVariableDiscount();

            // LOADING THE CUSTOMER GROUP ID AND ITS DISCOUNTS
            $group = $loaderCustomerGroup->getCustomerGroupById((int)$customerGroup);
            $this->groupFixed = array($group->getFixedDiscount());
            $this->groupVariable = array($group->getVariableDiscount());

            // PARENT ID!!! 
            $parentID = $group->getParentId();

            // Parent ID's start from 1
            // So if the the parent ID is bigger 0, we check if it has a fixed and variable discount
            // From the top: check if user ID is set, does it have a parent ID, if so what is the ID & is it bigger than 0, if so does it have a fixed or variable discount
            while ($parentID > 0) {
                $group = $loaderCustomerGroup->getCustomerGroupById((int)$parentID);
                $fixed = $group->getFixedDiscount();
                if (isset($fixed)) {
                    array_push($this->groupFixed, $group->getFixedDiscount());
                }
                $variable = $group->getVariableDiscount();
                if (isset($variable)) {
                    array_push($this->groupVariable, $group->getVariableDiscount());
                }
                $parentID = $group->getParentId();
            }
        }
    }

    public function getPrice()
    {
        $loader = new ProductLoader();
        $allProducts = $loader->getAllProducts();

        if (isset($this->idProduct)) {
            $product = $loader->getProductById((int)$_POST["product"]);
            $this->price = $product->getPrice();
        }
    }

    public function calculatorFunc()
    {
        $this->getDisc();
        $this->getPrice();

        // max — returns the highest value highest value
        $this->maxVarGroupDisc = max($this->groupVariable);
        // array_sum will calculate the addition of all discounts pushed to the array
        $this->sumFixedGroupDisc = array_sum($this->groupFixed);

        // if the max group discount is bigger than variable discount return the group discount, else return customer variable discount
        if ($this->maxVarGroupDisc > $this->customerVariable) {
            $this->bestVarDisc = $this->maxVarGroupDisc;
        } else {
            $this->bestVarDisc = $this->customerVariable;
        }
        
        // final price calculation
        $this->finalPrice = (($this->price - ($this->customerFixed * 100) - ($this->sumFixedGroupDisc * 100)) *  (1 - $this->bestVarDisc / 100)) / 100;
        
        // round -> returns a float, 2 (= precision), returns 2 digits after the comma
        $this->finalPrice = round($this->finalPrice, 2);

        // THE PRICE CAN NEVER BE NEGATIVE
        // IF FINALPRICE IS SMALLER THAN 0 WE SET IT TO ZERO.

        if($this->finalPrice < 0) {
            $this->finalPrice = 0;
        }

    }

    // GETTERS

    public function getIdCustomer(): int
    {
        return $this->idCustomer;
    }

    public function getIdProduct(): int
    {
        return $this->idProduct;
    }

    public function getCustomerFixed(): int
    {
        return $this->customerFixed;
    }

    public function getCustomerVariable(): int
    {
        return $this->customerVariable;
    }

    public function getGroupFixed(): array
    {
        return $this->groupFixed;
    }

    public function getSumFixedGroupDisc(): int
    {
        return $this->sumFixedGroupDisc;
    }

    public function getGroupVariable(): array
    {
        return $this->groupVariable;
    }

    public function getmaxVarGroupDisc(): int
    {
        return $this->maxVarGroupDisc;
    }

    public function getPrice2(): float
    {
        return $this->price;
    }

    public function getBestGroupDisc(): string
    {
        return $this->bestGroupDisc;
    }

    public function getBestVarDisc(): int
    {
        return $this->bestVarDisc;
    }

    public function getFinalPrice(): float
    {
        return $this->finalPrice;
    }
}
