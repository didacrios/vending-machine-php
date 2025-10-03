# 🧚 User Story
As a customer
I want to return my inserted money if I change my mind
So that I can get my money back

## ⚡ Acceptance Tests

### Scenario: Return coins before purchase
```gherkin
Given I have inserted 0.10
And I have inserted 0.10
When I select RETURN-COIN
Then I should receive 0.10 and 0.10
And no item should be dispensed
```

### Scenario: Return coins after partial insertion
```gherkin
Given I have inserted 0.25
And I have inserted 0.25
And I have inserted 0.10
When I select RETURN-COIN
Then I should receive 0.25, 0.25, and 0.10
And no item should be dispensed
```

### Scenario: Return coins with mixed denominations
```gherkin
Given I have inserted 1.00
And I have inserted 0.25
And I have inserted 0.10
And I have inserted 0.05
When I select RETURN-COIN
Then I should receive 1.00, 0.25, 0.10, and 0.05
And no item should be dispensed
```

### Scenario: Return coins when no money inserted
```gherkin
Given I have not inserted any money
When I select RETURN-COIN
Then I should receive no coins
And no item should be dispensed
```

### Scenario: Return coins after failed purchase attempt
```gherkin
Given the vending machine has Water available at 0.65
And I have inserted 0.25
And I have attempted to purchase Water
When I select RETURN-COIN
Then I should receive 0.25
And no item should be dispensed
```
