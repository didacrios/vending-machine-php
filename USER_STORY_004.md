# 🧚 User Story
As a service person
I want to restock items and change in the machine
So that customers can continue to use the vending machine

## ⚡ Acceptance Tests

### Scenario: Service person restocks items
```gherkin
Given the service person has access to the machine
When the service person selects SERVICE
And sets Water count to 10
And sets Juice count to 5
And sets Soda count to 3
Then the machine should have 10 Water items
And the machine should have 5 Juice items
And the machine should have 3 Soda items
```

### Scenario: Service person restocks change
```gherkin
Given the service person has access to the machine
When the service person selects SERVICE
And sets 0.05 coins to 20
And sets 0.10 coins to 15
And sets 0.25 coins to 10
And sets 1.00 coins to 5
Then the machine should have 20 coins of 0.05
And the machine should have 15 coins of 0.10
And the machine should have 10 coins of 0.25
And the machine should have 5 coins of 1.00
```

### Scenario: Service person restocks both items and change
```gherkin
Given the service person has access to the machine
When the service person selects SERVICE
And sets Water count to 15
And sets Juice count to 8
And sets Soda count to 5
And sets 0.05 coins to 30
And sets 0.10 coins to 25
And sets 0.25 coins to 20
And sets 1.00 coins to 10
Then the machine should have 15 Water items
And the machine should have 8 Juice items
And the machine should have 5 Soda items
And the machine should have 30 coins of 0.05
And the machine should have 25 coins of 0.10
And the machine should have 20 coins of 0.25
And the machine should have 10 coins of 1.00
```

### Scenario: Service person checks current inventory
```gherkin
Given the service person has access to the machine
And the machine currently has 3 Water items
And the machine currently has 1 Juice item
And the machine currently has 0 Soda items
When the service person selects SERVICE
Then the service person should see 3 Water items
And the service person should see 1 Juice item
And the service person should see 0 Soda items
```

### Scenario: Service person exits service mode
```gherkin
Given the service person has access to the machine
And the service person is in SERVICE mode
When the service person exits SERVICE mode
Then the machine should return to normal operation
And customers should be able to use the machine
```
