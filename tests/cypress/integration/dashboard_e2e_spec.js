describe("Dashboard (E2E)", function () {
  beforeEach(function () {
    cy.login();
  });

  it("shows upcoming reminders on the dashboard (RF-010.1)", function () {
    cy.createContact("Grace", "Hopper", "Woman");

    cy.get('a[href*="/reminders/add"]').click();
    cy.get("input[name=title]").type("Llamada de seguimiento");
    cy.get("#frequency_type_once").click();
    cy.get("button[type=submit]").click();

    cy.visit("/dashboard");

    cy.contains("Llamada de seguimiento").should("exist");
    cy.contains("Grace Hopper").should("exist");
  });
});
