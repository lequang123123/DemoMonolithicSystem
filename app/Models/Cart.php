<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cart extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'session_id',
    ];

    /**
     * Get the user that owns the cart.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the cart items for the cart.
     */
    public function cartItems()
    {
        return $this->hasMany(CartItem::class);
    }

    /**
     * Get the total amount of the cart.
     */
    public function getTotalAmountAttribute()
    {
        return $this->cartItems->sum(function ($item) {
            return $item->quantity * $item->price;
        });
    }

    /**
     * Get the total quantity of items in the cart.
     */
    public function getTotalQuantityAttribute()
    {
        return $this->cartItems->sum('quantity');
    }

    /**
     * Add item to cart.
     */
    public function addItem($productId, $quantity = 1, $price = null)
    {
        $existingItem = $this->cartItems()->where('product_id', $productId)->first();

        if ($existingItem) {
            $existingItem->update([
                'quantity' => $existingItem->quantity + $quantity
            ]);
        } else {
            $product = Product::find($productId);
            $this->cartItems()->create([
                'product_id' => $productId,
                'quantity' => $quantity,
                'price' => $price ?? $product->effective_price,
            ]);
        }
    }

    /**
     * Remove item from cart.
     */
    public function removeItem($productId)
    {
        $this->cartItems()->where('product_id', $productId)->delete();
    }

    /**
     * Clear all items from cart.
     */
    public function clear()
    {
        $this->cartItems()->delete();
    }
} 