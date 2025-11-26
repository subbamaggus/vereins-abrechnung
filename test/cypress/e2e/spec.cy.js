describe('template spec', () => {
    it('passes', () => {
        cy.visit('https://example.cypress.io')
    }),
    it('passes', () => {
        cy.visit('http://www.weggefoehnt.de/privat/verrechnung/')
        cy.contains('uebersicht')
    }),
    it('passes', () => {
        cy.visit('http://www.weggefoehnt.de/budget/kohle/', {
            auth: {
                username: 'maggus',
                password: 'antonius'
            }
        })
    }),
    it('passes', () => {
        cy.visit('https://example.cypress.io')
        cy.contains('Kitchen')
    })  
})