describe("Contact management (E2E)", function () {
  beforeEach(function () {
    cy.login();
  });

  it.only("lets you create a new contact (RF-003.1)", function () {
    cy.createContact("Taylor", "Otwell", "Man");

    cy.url().should("include", "/people/h:");
    cy.get("h1").should("contain", "Taylor Otwell");
  });

  it("lets you edit contact information (RF-003.2)", function () {
    cy.createContact("John", "Doe", "Man");

    cy.get("#button-edit-contact").click();
    cy.url().should("include", "/edit");

    cy.get("input[name=firstname]").clear().type("Jane");
    cy.get("input[name=lastname]").clear().type("Smith");
    cy.get("button[name=save]").click();

    cy.url().should("include", "/people/h:");
    cy.get("h1").should("contain", "Jane Smith");
  });

  it("lets you delete a contact with confirmation (RF-003.3)", function () {
    cy.createContact("Mark", "Delete", "Man");

    cy.url().should("include", "/people/h:");
    cy.get("#link-delete-contact").click();

    cy.url().should("include", "/people");

    cy.visit("/people");
    cy.get(".people-list-item").should("not.contain", "Mark Delete");
  });
});
