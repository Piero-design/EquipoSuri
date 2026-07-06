describe("Reminder management (E2E)", function () {
  beforeEach(function () {
    cy.login();
  });

  it("lets you create a reminder associated to a contact (RF-004.1)", function () {
    cy.createContact("Ada", "Lovelace", "Woman");

    // profile_active_tab por defecto es 'notes', donde vive la sección
    // de recordatorios (módulo activo por defecto en cuentas nuevas).
    cy.get('a[href*="/reminders/add"]').click();
    cy.url().should("include", "/reminders/add");

    cy.get("input[name=title]").type("Renovar pasaporte");
    cy.get("#frequency_type_once").click();
    cy.get("button[type=submit]").click();

    cy.url().should("include", "/people/h:");
    cy.contains("Renovar pasaporte").should("exist");
  });
});
