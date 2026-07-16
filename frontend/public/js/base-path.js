// RentaFlow base path fix for subdirectory deployments
(function () {
  // Detect base path from current URL
  const path = window.location.pathname;
  const base = path.replace(/\/[^\/]*$/, ""); // remove filename
  window.RENTAFLOW_BASE = base;

  // Patch fetch to rewrite root-relative /api/* URLs
  const originalFetch = window.fetch;
  window.fetch = function (url, options) {
    if (typeof url === "string" && url.startsWith("/api/")) {
      url = base + url;
    }
    return originalFetch.call(this, url, options);
  };

  // Override XMLHttpRequest open() to rewrite /api paths
  const OriginalXHR = window.XMLHttpRequest;
  window.XMLHttpRequest = function () {
    const xhr = new OriginalXHR();
    const originalOpen = xhr.open;
    xhr.open = function (method, url, ...args) {
      if (typeof url === "string" && url.startsWith("/api/")) {
        url = base + url;
      }
      return originalOpen.call(this, method, url, ...args);
    };
    return xhr;
  };
})();
