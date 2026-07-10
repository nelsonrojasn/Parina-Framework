# FSP Rules Engine Syntax & Usage Guide

The **FSP Rules Engine** is a lightweight, secure, sandbox-isolated virtual machine designed to process business logic using a custom Domain-Specific Language (DSL) in **Prefix Notation**.

This document outlines the grammar, variables, prefix operators, control structures, and outputs of the FSP DSL.

---

## 1. Syntax & Language Grammar

An FSP script consists of declarations, assignment statements, conditional structures, and result expressions.

### Declarations
*   `formula <RuleName>`: Optional block header declaring the rule name.
*   `begin`: Denotes the start of execution.
*   `end`: Denotes the end of a block (loops, conditionals, or the program itself).

### Variables & Data Types
Variables are locally scoped, case-sensitive, dynamically typed, and declared on assignment.
*   **Strings**: Double-quoted literals, e.g., `"In Transit"`, `"B2B_Discount"`. Commas, parenthesis, and hash characters (`#`) are fully supported within string boundaries.
*   **Numbers**: Numeric literals, e.g., `100` (integer) or `45.99` (float).
*   **Booleans**: Case-insensitive literals, `true` and `false`.
*   **Parameters**: Inputs injected into the engine at runtime are prefixed with `param.`, e.g., `param.distance`, `param.subtotal`.

### Assignments
Assign values to local variables using the `=` operator:
```text
local_var = "value"
total_price = *(param.quantity, param.price)
```

### Prefix Operators
Operations use **prefix notation** (Polish notation) format: `operator(argument_1, argument_2)`.

| Operator | Type | Name | Example |
| :--- | :--- | :--- | :--- |
| `+` | Arithmetic | Addition | `+(price, tax)` |
| `-` | Arithmetic | Subtraction | `-(subtotal, discount)` |
| `*` | Arithmetic | Multiplication | `*(quantity, unit_price)` |
| `/` | Arithmetic | Division | `/(total, parts)` |
| `%` | Arithmetic | Modulo | `%(counter, 2)` |
| `==` | Comparison | Equality | `==(param.status, "active")` |
| `~=` | Comparison | Inequality | `~=(role, "guest")` |
| `<` | Comparison | Less Than | `<(age, 18)` |
| `<=` | Comparison | Less Than or Equal | `<=(mileage, 5000)` |
| `>` | Comparison | Greater Than | `>(amount, 1000)` |
| `>=` | Comparison | Greater Than or Equal | `>=(param.score, 85)` |
| `&&` | Logical | AND | `&&(is_member, >=(total, 50))` |
| `||` | Logical | OR | `||(is_vip, is_employee)` |

### Control Flow (`if` / `else` / `end`)
Allows conditional branching. Blocks can be nested inside each other. Jumps are pre-calculated for $O(1)$ efficiency.
```text
if >=(param.subtotal, 1000)
    discount = 100
else
    discount = 0
end
```

### Output Results
Declares which variables are returned in the output payload using the `result <<` statement:
```text
result << discount, total_price
```

---

## 2. Interactive Examples

We have created three production-ready example rulesets in the repository. You can execute them using the CLI tool:

### Example A: Travel Workflow Stages
Calculates the status stage and tracking details of a trip based on distance, country boundaries, and transportation status.
*   **Rule File**: [travel_workflow.fsp](file:///home/nelson/repos/parina/docs/examples/travel_workflow.fsp)
*   **Parameters**: [travel_workflow_params.json](file:///home/nelson/repos/parina/docs/examples/travel_workflow_params.json)
*   **Execution Command**:
    ```bash
    php bin/fsp-tester.php docs/examples/travel_workflow.fsp docs/examples/travel_workflow_params.json
    ```

### Example B: Pricing Discount Offer
Calculates promotional pricing discounts, applying tiered volume discounts and optional coupon codes.
*   **Rule File**: [offer_pricing.fsp](file:///home/nelson/repos/parina/docs/examples/offer_pricing.fsp)
*   **Parameters**: [offer_pricing_params.json](file:///home/nelson/repos/parina/docs/examples/offer_pricing_params.json)
*   **Execution Command**:
    ```bash
    php bin/fsp-tester.php docs/examples/offer_pricing.fsp docs/examples/offer_pricing_params.json
    ```

### Example C: Monthly Insurance Premium
Calculates monthly insurance premium pricing dynamically according to tiers of the insured amount.
*   **Rule File**: [insurance_premium.fsp](file:///home/nelson/repos/parina/docs/examples/insurance_premium.fsp)
*   **Parameters**: [insurance_premium_params.json](file:///home/nelson/repos/parina/docs/examples/insurance_premium_params.json)
*   **Execution Command**:
    ```bash
    php bin/fsp-tester.php docs/examples/insurance_premium.fsp docs/examples/insurance_premium_params.json
    ```
