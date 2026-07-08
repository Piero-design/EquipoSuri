// tests/cypress/integration/dashboard_e2e_spec.js
describe("Dashboard (E2E)", function () {
  beforeEach(function () {
    cy.login();
  });

  it("shows upcoming reminders on the dashboard (RF-010.1)", function () {
    cy.createContact("Grace", "Hopper", "Woman");

    cy.get('a[href*="/reminders/create"]').first().click();

    cy.get("input[name=title]").type("Llamada de seguimiento");

    // Se evita la fecha "hoy" por defecto: un reminder one_time con
    // initial_date = hoy dispara un bug conocido en DateHelper::
    // addTimeAccordingToFrequencyType(), que trata 'one_time' como
    // caso "default" y le suma un año en vez de dejarlo en el día.
    cy.get("input[name=initial_date]")
      .invoke("val", getTomorrowDateString())
      .trigger("change");

    cy.get("#frequency_type_once").click();
    cy.get("button[type=submit]").click();

    cy.visit("/dashboard");

    cy.contains("Llamada de seguimiento").should("exist");

    cy.contains("Grace H").should("exist");
  });
});

function getTomorrowDateString() {
  const d = new Date();
  d.setDate(d.getDate() + 1);
  return d.toISOString().split("T")[0];
}
