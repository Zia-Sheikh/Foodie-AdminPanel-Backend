<?php

namespace App\Admin\Controllers;

use App\Models\OrderItem;
use App\Models\Order;
use App\Models\Item;
use App\Models\ItemOption;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Show;

class OrderItemController extends AdminController
{
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'Order Item';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new OrderItem());

        $grid->column('id', __('Id'));
        $grid->column('order_id', __('Order '));
        $grid->column('item_id', __('Item '))->display(function($item_id){
            return Item::where('id',$item_id)->value('item_name');
        });
        $grid->column('item_name', __('Item Name'));
        $grid->column('item_option_id', __('Item Option'))->display(function($item_option_id){
            return ItemOption::where('id',$item_option_id)->value('option_name');
        });
        $grid->column('quantity', __('Quantity'));
        $grid->column('price_per_item', __('Price Per Item'));
        $grid->column('item_option_price', __('Item Option Price'));
        $grid->column('total', __('Total'));
     
        $grid->disableExport();
        $grid->disableActions();
        $grid->filter(function ($filter) {
            //Get All status
            $orders = Order::pluck('id','id');
            $items = Item::pluck('item_name', 'id');
    
            $filter->equal('order_id', __('Order id'))->select($orders);
            $filter->equal('item_id', __('Item id'))->select($items);
            $filter->like('item_name', __('Item name'));
            $filter->like('quantity', __('Quantity'));
            $filter->like('price_per_item', __('Price per item'));
        });
        return $grid;
    }
}
