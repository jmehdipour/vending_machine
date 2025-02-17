# Vending Machine API Documentation

## Overview
This project is a vending machine system with a back-end API built using PHP Lumen. The API allows interaction with multiple vending machines, each capable of processing coin insertions, managing product selections, and dispensing products. The system uses a MySQL relational database for data storage and supports unit and feature tests.

## Features
- **Vending Machines**: Several vending machines interact with the back-end API.
- **State Management**: Machines transition between states (`idle`, `processing`) based on coin insertion and product selection.
- **Database Interaction**: The API communicates with a MySQL database through repositories.
- **Testing**: The project includes feature tests for the endpoints and unit tests for controllers and repositories.
- **Docker Compose**: The project uses Docker Compose to simplify testing and development environments.

## Endpoints

### 1. `GET /api/machines`
Retrieve a list of all vending machines.

- **Description**: This endpoint returns the list of all vending machines in the system.
- **Response**:
    - `200 OK`: A list of vending machines.
    - Example Response:
      ```json
      [
        {
          "id": 1,
          "name": "Vending Machine 1",
          "location": "Location 1",
          "status": "idle"
        },
        {
          "id": 2,
          "name": "Vending Machine 2",
          "location": "Location 2",
          "status": "idle"
        }
      ]
      ```

### 2. `POST /api/machines/{machine_id}/insert-coin`
Insert a coin into the specified vending machine.

- **Description**: This endpoint triggers the machine to transition to the `processing` state, allowing the user to select a product.
- **Parameters**:
    - `machine_id`: The ID of the vending machine.
- **Response**:
    - `200 OK`: Coin inserted successfully.
    - `404 Not Found`: Machine not found.
    - `400 Bad Request`: Machine is busy (in `processing` state).

### 3. `GET /api/machines/{machine_id}/products`
Get the list of products available in a specific vending machine.

- **Description**: This endpoint returns the list of products available in the specified machine.
- **Parameters**:
    - `machine_id`: The ID of the vending machine.
- **Response**:
    - `200 OK`: A list of products.
    - Example Response:
      ```json
      [
        {
          "id": 1,
          "name": "Soda",
          "stock": 10
        },
        {
          "id": 2,
          "name": "Tea",
          "stock": 5
        }
      ]
      ```
    - `404 Not Found`: No products found for this machine.

### 4. `POST /api/machines/{machine_id}/select-product/{product_id}`
Select a product from the specified vending machine.

- **Description**: This endpoint allows the user to select a product from the available options after inserting a coin.
- **Parameters**:
    - `machine_id`: The ID of the vending machine.
    - `product_id`: The ID of the product to be selected.
- **Response**:
    - `200 OK`: Product dispensed successfully.
    - `404 Not Found`: Machine or product not found.
    - `400 Bad Request`: Machine is not ready (in `idle` state) or the product is out of stock.

## Database Schema

### Machine
```json
{
    "id": "integer",
    "name": "string",
    "location": "string",
    "status": "string"
}
```
- `id`: The unique identifier of the machine.
- `name`: The name of the vending machine.
- `location`: The physical location of the machine.
- `status`: The current state of the machine (`idle`, `processing`).

### Product
```json
{
  "id": "integer",
  "name": "string"
}
```
- `id`: The unique identifier of the product.
- `name`: The name of the product (e.g., soda, tea).

### MachineProduct
```json
{
  "id": "integer",
  "machine_id": "integer",
  "product_id": "integer",
  "stock": "integer"
}
```
- `id`: The unique identifier for the machine-product relation.
- `machine_id`: The ID of the related vending machine.
- `product_id`: The ID of the related product.
- `stock`: The number of available units of the product in the vending machine.

### Transaction
```json
{
  "id": "integer",
  "machine_id": "integer",
  "product_id": "integer"
}
```
- `id`: The unique identifier for the transaction.
- `machine_id`: The ID of the vending machine involved in the transaction.
- `product_id`: The ID of the product dispensed in the transaction.

## Explanation

- **Machines Table**: Stores information about vending machines, including the machine's name, location, and status (`idle` or `processing`).

- **Products Table**: Stores information about products available in the vending machines, such as their name.

- **MachineProduct Table**: Represents the relationship between vending machines and products, storing how many of each product are available in each machine.

- **Transactions Table**: Stores records of each transaction that occurs, linking a machine to a product that has been dispensed.

This schema ensures a robust management system for the vending machine's operations, enabling you to track products, machine statuses, and transactions effectively.

## Testing

The project includes both feature tests for the API endpoints and unit tests for controllers and repositories.

### Feature Tests
- These tests ensure that the API endpoints work as expected, including checking responses and status codes for various scenarios.

### Unit Tests
- Unit tests validate the logic in controllers and repositories, ensuring that data handling and machine state transitions work correctly.

### Docker Compose
- The project uses Docker Compose to set up an isolated environment for testing and development, including the MySQL database.

## Conclusion
This API allows vending machines to interact with users by managing coin insertions, product selections, and state transitions. The system is designed to be robust and easy to test, with clear separation between the controller logic, repositories, and database interactions.

