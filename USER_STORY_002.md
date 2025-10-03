# 🧚 User Story
As a customer
I want to receive change when I overpay for an item
So that I don't lose money

## ⚡ Acceptance Tests

### Scenario: Purchase item with overpayment
```gherkin
Given the vending machine has Water available at 0.65
And the machine has sufficient change
And I have inserted 1.00
When I select GET-WATER
Then I should receive Water
And I should receive 0.25 and 0.10 as change
```

### Scenario: Purchase Juice with overpayment
```gherkin
Given the vending machine has Juice available at 1.00
And the machine has sufficient change
And I have inserted 1.00
And I have inserted 0.25
When I select GET-JUICE
Then I should receive Juice
And I should receive 0.25 as change
```

### Scenario: Purchase Soda with overpayment
```gherkin
Given the vending machine has Soda available at 1.50
And the machine has sufficient change
And I have inserted 1.00
And I have inserted 1.00
When I select GET-SODA
Then I should receive Soda
And I should receive 0.25 and 0.25 as change
```

### Scenario: Insufficient change for overpayment
```gherkin
Given the vending machine has Water available at 0.65
And the machine has no change available
And I have inserted 1.00
When I select GET-WATER
Then no item should be dispensed
And my inserted money should remain in the machine
```

### Scenario: Complex change calculation
```gherkin
Given the vending machine has Water available at 0.65
And the machine has sufficient change
And I have inserted 1.00
And I have inserted 0.25
When I select GET-WATER
Then I should receive Water
And I should receive 0.50 as change
```
