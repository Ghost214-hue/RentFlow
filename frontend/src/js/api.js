/**
 * RentFlow API Client
 * Handles all communication with the backend
 */
// Auto-detect base path from current page URL (e.g., /RentFlow/api)
const scriptPath = document.currentScript ? document.currentScript.src : "";
const API_BASE =
  (scriptPath.includes("/RentFlow/") ? "/RentFlow" : "") + "/api";

const Api = {
  token: localStorage.getItem("rf_token") || null,

  setToken(token) {
    this.token = token;
    if (token) {
      localStorage.setItem("rf_token", token);
    } else {
      localStorage.removeItem("rf_token");
    }
  },

  getHeaders() {
    const headers = { "Content-Type": "application/json" };
    if (this.token) {
      headers["Authorization"] = `Bearer ${this.token}`;
    }
    return headers;
  },

  async request(method, endpoint, data = null) {
    const config = {
      method,
      headers: this.getHeaders(),
    };
    if (data) {
      config.body = JSON.stringify(data);
    }

    const response = await fetch(`${API_BASE}${endpoint}`, config);
    const json = await response.json();

    if (!response.ok) {
      throw new Error(json.error || "Request failed");
    }

    return json;
  },

  // Auth
  login(email, password) {
    return this.request("POST", "/auth/login", { email, password });
  },

  register(data) {
    return this.request("POST", "/auth/register", data);
  },

  logout() {
    return this.request("POST", "/auth/logout");
  },

  getProfile() {
    return this.request("GET", "/auth/me");
  },

  // Dashboard
  getDashboard() {
    return this.request("GET", "/dashboard");
  },

  // Properties
  getProperties() {
    return this.request("GET", "/properties");
  },

  getProperty(id) {
    return this.request("GET", `/properties/${id}`);
  },

  createProperty(data) {
    return this.request("POST", "/properties", data);
  },

  updateProperty(id, data) {
    return this.request("PUT", `/properties/${id}`, data);
  },

  deleteProperty(id) {
    return this.request("DELETE", `/properties/${id}`);
  },

  // Houses
  getHouses(params = {}) {
    const query = new URLSearchParams(params).toString();
    return this.request("GET", `/houses${query ? "?" + query : ""}`);
  },

  createHouse(data) {
    return this.request("POST", "/houses", data);
  },

  updateHouse(id, data) {
    return this.request("PUT", `/houses/${id}`, data);
  },

  deleteHouse(id) {
    return this.request("DELETE", `/houses/${id}`);
  },

  // Tenants
  getTenants() {
    return this.request("GET", "/tenants");
  },

  getTenant(id) {
    return this.request("GET", `/tenants/${id}`);
  },

  createTenant(data) {
    return this.request("POST", "/tenants", data);
  },

  updateTenant(id, data) {
    return this.request("PUT", `/tenants/${id}`, data);
  },

  deleteTenant(id) {
    return this.request("DELETE", `/tenants/${id}`);
  },

  // Payments
  getPayments() {
    return this.request("GET", "/payments");
  },

  getPayment(id) {
    return this.request("GET", `/payments/${id}`);
  },

  createPayment(data) {
    return this.request("POST", "/payments", data);
  },

  // Bills
  getBills(month) {
    const query = month ? `?month=${month}` : "";
    return this.request("GET", `/bills${query}`);
  },

  getBill(id) {
    return this.request("GET", `/bills/${id}`);
  },

  generateBills(data) {
    return this.request("POST", "/bills/generate", data);
  },

  // Complaints
  getComplaints(params = {}) {
    const query = new URLSearchParams(params).toString();
    return this.request("GET", `/complaints${query ? "?" + query : ""}`);
  },

  getComplaint(id) {
    return this.request("GET", `/complaints/${id}`);
  },

  createComplaint(data) {
    return this.request("POST", "/complaints", data);
  },

  updateComplaint(id, data) {
    return this.request("PUT", `/complaints/${id}`, data);
  },

  // Communications
  getCommunications(params = {}) {
    const query = new URLSearchParams(params).toString();
    return this.request("GET", `/communications${query ? "?" + query : ""}`);
  },

  sendCommunication(data) {
    return this.request("POST", "/communications", data);
  },

  getTemplates() {
    return this.request("GET", "/templates");
  },

  // Caretakers
  getCaretakers() {
    return this.request("GET", "/caretakers");
  },

  createCaretaker(data) {
    return this.request("POST", "/caretakers", data);
  },

  updateCaretaker(id, data) {
    return this.request("PUT", `/caretakers/${id}`, data);
  },

  deleteCaretaker(id) {
    return this.request("DELETE", `/caretakers/${id}`);
  },

  // Reports
  getReports() {
    return this.request("GET", "/reports");
  },
};
