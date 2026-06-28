/**
 * Persistence utilities for the store using localStorage.
 */

export interface StoreCart {
  [productId: string]: number;
}

const CART_KEY = "rentflow_cart";

export function loadCart(): StoreCart | null {
  try {
    const raw = localStorage.getItem(CART_KEY);
    if (!raw) return null;
    return JSON.parse(raw) as StoreCart;
  } catch (e) {
    console.error("Failed to load cart from localStorage", e);
    return null;
  }
}

export function saveCart(cart: StoreCart): void {
  try {
    localStorage.setItem(CART_KEY, JSON.stringify(cart));
  } catch (e) {
    console.error("Failed to save cart to localStorage", e);
  }
}

export function clearCart(): void {
  try {
    localStorage.removeItem(CART_KEY);
  } catch (e) {
    console.error("Failed to clear cart from localStorage", e);
  }
}
