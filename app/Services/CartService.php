<?php

namespace App\Services;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Product;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class CartService
{
    /**
     * Get or create cart for the current user/session.
     */
    public function getCart(): Cart
    {
        if (Auth::check()) {
            return Cart::firstOrCreate([
                'user_id' => Auth::id()
            ]);
        }

        $sessionId = Session::getId();
        return Cart::firstOrCreate([
            'session_id' => $sessionId
        ]);
    }

    /**
     * Add item to cart.
     */
    public function addItem(int $productId, int $quantity = 1): bool
    {
        $product = Product::find($productId);
        
        if (!$product || !$product->isInStock()) {
            return false;
        }

        $cart = $this->getCart();
        
        // Check if item already exists in cart
        $existingItem = $cart->cartItems()->where('product_id', $productId)->first();
        
        if ($existingItem) {
            $newQuantity = $existingItem->quantity + $quantity;
            
            // Check stock availability
            if ($newQuantity > $product->stock_quantity) {
                return false;
            }
            
            $existingItem->update([
                'quantity' => $newQuantity
            ]);
        } else {
            // Check stock availability
            if ($quantity > $product->stock_quantity) {
                return false;
            }
            
            $cart->cartItems()->create([
                'product_id' => $productId,
                'quantity' => $quantity,
                'price' => $product->effective_price
            ]);
        }

        return true;
    }

    /**
     * Update item quantity in cart.
     */
    public function updateItemQuantity(int $cartItemId, int $quantity): bool
    {
        $cart = $this->getCart();
        $cartItem = $cart->cartItems()->find($cartItemId);
        
        if (!$cartItem) {
            return false;
        }

        $product = $cartItem->product;
        
        if ($quantity > $product->stock_quantity) {
            return false;
        }

        if ($quantity <= 0) {
            $cartItem->delete();
        } else {
            $cartItem->update(['quantity' => $quantity]);
        }

        return true;
    }

    /**
     * Remove item from cart.
     */
    public function removeItem(int $cartItemId): bool
    {
        $cart = $this->getCart();
        $cartItem = $cart->cartItems()->find($cartItemId);
        
        if (!$cartItem) {
            return false;
        }

        $cartItem->delete();
        return true;
    }

    /**
     * Clear all items from cart.
     */
    public function clearCart(): bool
    {
        $cart = $this->getCart();
        $cart->cartItems()->delete();
        return true;
    }

    /**
     * Get cart items count.
     */
    public function getItemsCount(): int
    {
        $cart = $this->getCart();
        return $cart->cartItems->sum('quantity');
    }

    /**
     * Get cart total amount.
     */
    public function getTotalAmount(): float
    {
        $cart = $this->getCart();
        return $cart->cartItems->sum(function ($item) {
            return $item->quantity * $item->price;
        });
    }

    /**
     * Get cart items with product details.
     */
    public function getCartItems()
    {
        $cart = $this->getCart();
        return $cart->cartItems()->with('product')->get();
    }

    /**
     * Apply discount code.
     */
    public function applyDiscount(string $discountCode): array
    {
        // Implement discount logic here
        // This is a simplified example
        $validCodes = [
            'SAVE10' => 10,
            'SAVE20' => 20,
            'WELCOME' => 15
        ];

        if (!isset($validCodes[$discountCode])) {
            return [
                'success' => false,
                'message' => 'Invalid discount code'
            ];
        }

        $discountPercentage = $validCodes[$discountCode];
        $totalAmount = $this->getTotalAmount();
        $discountAmount = ($totalAmount * $discountPercentage) / 100;

        return [
            'success' => true,
            'discount_percentage' => $discountPercentage,
            'discount_amount' => $discountAmount,
            'total_after_discount' => $totalAmount - $discountAmount
        ];
    }

    /**
     * Validate cart before checkout.
     */
    public function validateCart(): array
    {
        $cart = $this->getCart();
        $cartItems = $cart->cartItems()->with('product')->get();
        
        $errors = [];

        foreach ($cartItems as $item) {
            $product = $item->product;
            
            if (!$product || !$product->isInStock()) {
                $errors[] = "Product '{$product->name}' is no longer available";
                continue;
            }

            if ($item->quantity > $product->stock_quantity) {
                $errors[] = "Only {$product->stock_quantity} units of '{$product->name}' available";
            }

            // Check if price has changed
            if ($item->price != $product->effective_price) {
                $errors[] = "Price of '{$product->name}' has changed";
            }
        }

        return [
            'is_valid' => empty($errors),
            'errors' => $errors
        ];
    }

    /**
     * Merge guest cart with user cart after login.
     */
    public function mergeGuestCart(string $sessionId, int $userId): bool
    {
        $guestCart = Cart::where('session_id', $sessionId)->first();
        
        if (!$guestCart) {
            return true;
        }

        $userCart = Cart::firstOrCreate(['user_id' => $userId]);
        
        foreach ($guestCart->cartItems as $guestItem) {
            $existingItem = $userCart->cartItems()
                ->where('product_id', $guestItem->product_id)
                ->first();
            
            if ($existingItem) {
                $existingItem->update([
                    'quantity' => $existingItem->quantity + $guestItem->quantity
                ]);
            } else {
                $userCart->cartItems()->create([
                    'product_id' => $guestItem->product_id,
                    'quantity' => $guestItem->quantity,
                    'price' => $guestItem->price
                ]);
            }
        }

        $guestCart->delete();
        return true;
    }
} 