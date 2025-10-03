# 🧚 User Story
As a customer
I want to insert money and purchase items
So that I can get the products I need

## ⚡ Acceptance Tests

### Scenario: Purchase item with exact change
```gherkin
Given the vending machine has Water available at 0.65
And the machine has sufficient change
And I have inserted 0.65
When I select GET-WATER
Then I should receive Water
And no change should be returned
```

### Scenario: Purchase Soda with exact change
```gherkin
Given the vending machine has Soda available at 1.50
And the machine has sufficient change
And I have inserted 1.00
And I have inserted 0.25
And I have inserted 0.25
When I select GET-SODA
Then I should receive Soda
And no change should be returned
```

### Scenario: Purchase Juice with exact change
```gherkin
Given the vending machine has Juice available at 1.00
And the machine has sufficient change
And I have inserted 1.00
When I select GET-JUICE
Then I should receive Juice
And no change should be returned
```

### Scenario: Insufficient funds for purchase
```gherkin
Given the vending machine has Water available at 0.65
And I have inserted 0.25
When I select GET-WATER
Then no item should be dispensed
And no change should be returned
And my inserted money should remain in the machine
```

### Scenario: Item out of stock
```gherkin
Given the vending machine has 0 Water items available
And I have inserted 0.65
When I select GET-WATER
Then no item should be dispensed
And my inserted money should remain in the machine
```
