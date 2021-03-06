describe('Edit profile', function () {
  beforeEach(() => {
    cy.fixture('auth/tenant.json').as('tenant')
    cy.fixture('auth/customerUser.json').as('user')
    cy.fixture('user-mgmt/roles.json').as('roles')
    cy.fixture('user-mgmt/company.json').as('company')
    cy.fixture('user-mgmt/user-list.json').as('users')
    cy.fixture('user-mgmt/edited-user.json').as('editedUser')
  })

  it('creates a user successfully', function () {
    cy.server()

    cy.route({ url: '**/api/user', response: this.user })

    cy.route({ url: '**/api/current-tenant', response: this.tenant })

    cy.route({ url: '**/api/roles', response: this.roles })

    cy.route({ url: '**/api/companies/2', response: this.company })

    cy.visit('localhost:8080/user/edit-profile')

    cy.get('[data-cy=change-password-button]').click({ force: true })

    cy.route({ url: '**/api/user', response: this.user })

    cy.route({ url: '**/api/current-tenant', response: this.tenant })

    cy.route({ url: '**/api/roles', response: this.roles })

    cy.route({ url: '**/api/companies/2', response: this.company })

    cy.visit('http://localhost:8080/user/dashboard/change-password/')

    cy.get('[data-cy=old-password-field]').type('mocked', { force: true })

    cy.get('[data-cy=password-field]').type('mockednewpass', { force: true })

    cy.get('[data-cy=password-confirmation-field]').type('mockednewpass', { force: true })

    cy.get('[data-cy=save-button]').click({ force: true })

    // Please note that there is something going on with this endpoint
    cy.route({ method: 'POST', url: '**/api/password/change', reponse: {} })
  })
})
