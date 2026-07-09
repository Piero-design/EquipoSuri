describe("Activity management (E2E)", function () {
  beforeEach(function () {
    cy.login();
  });

  it("lets you register an activity for a contact (RF-005.1)", function () {
    cy.createContact("Alan", "Turing", "Man");
    cy.createActivity();
  });
});
