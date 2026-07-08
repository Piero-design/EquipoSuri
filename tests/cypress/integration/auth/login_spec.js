// tests/cypress/integration/auth/login_spec.js
describe("Login", function () {
  it("lets a user sign in with valid credentials (RF-001.2)", function () {
    cy.visit("/login");

    cy.get("input[name=email]").type("admin@admin.com");
    cy.get("input[name=password]").type("admin0");
    cy.get("button[type=submit]").click();

    cy.url().should("include", "/dashboard");
    cy.contains("Welcome to your account!").should("exist");
  });

  it("should not let user sign in with a non-existing account (RF-001.2)", function () {
    cy.visit("/");

    cy.get("input[name=email]").type("impossibru@test.com");
    cy.get("input[name=password]").type("testtest");
    cy.get("button[type=submit]").click();

    cy.get(".alert").should("exist");
  });
});
