# Sample API Responses

Base URL: `http://localhost:8000/api/v1`

Authenticate with `Authorization: Bearer {token}` and `Accept: application/json`.

## POST /auth/login

```json
{
  "success": true,
  "message": "Logged in successfully.",
  "data": {
    "user": {
      "id": 1,
      "name": "Pump Admin",
      "email": "admin@pump.test",
      "phone": "03001234567",
      "role": "admin",
      "created_at": "2026-09-04T22:00:00.000000Z"
    },
    "token": "1|plainTextTokenHere",
    "token_type": "Bearer"
  }
}
```

## POST /shifts/start

```json
{
  "success": true,
  "message": "Shift started.",
  "data": {
    "id": 1,
    "user": {
      "id": 2,
      "name": "Shift Attendant",
      "email": "attendant@pump.test",
      "phone": "03007654321",
      "role": "attendant",
      "created_at": "2026-09-04T22:00:00.000000Z"
    },
    "closed_by": null,
    "start_time": "2026-09-05T06:00:00.000000Z",
    "end_time": null,
    "status": "open",
    "expected_cash": null,
    "credit_sales_amount": null,
    "notes": null,
    "meter_readings": [
      {
        "id": 1,
        "shift_id": 1,
        "nozzle_id": 1,
        "opening_reading": "10450.250",
        "closing_reading": null,
        "liters_sold": null,
        "opening_recorded_at": "2026-09-05T06:00:01.000000Z",
        "closing_recorded_at": null
      }
    ],
    "sales": []
  }
}
```

## POST /shifts/{id}/end

```json
{
  "success": true,
  "message": "Shift closed. Sales have been generated.",
  "data": {
    "id": 1,
    "status": "closed",
    "expected_cash": "21800.00",
    "credit_sales_amount": "5450.00",
    "sales": [
      {
        "id": 1,
        "shift_id": 1,
        "nozzle_id": 1,
        "liters_sold": "100.000",
        "rate_per_liter": "272.50",
        "total_amount": "27250.00",
        "cost_per_liter": "255.00",
        "total_cost": "25500.00",
        "profit": "1750.00"
      }
    ]
  }
}
```

## GET /dashboard

```json
{
  "success": true,
  "message": "OK",
  "data": {
    "date": "2026-09-05",
    "today_sales_amount": "27250.00",
    "today_liters": "100.000",
    "sales_by_fuel_type": [
      {
        "fuel_type_id": 1,
        "fuel_type": "Petrol",
        "code": "petrol",
        "liters_sold": "80.000",
        "total_amount": "21800.00",
        "profit": "1400.00"
      },
      {
        "fuel_type_id": 2,
        "fuel_type": "Diesel",
        "code": "diesel",
        "liters_sold": "20.000",
        "total_amount": "5316.00",
        "profit": "350.00"
      }
    ],
    "sales_by_nozzle": [
      {
        "nozzle_id": 1,
        "label": "Unit 1 Side A",
        "pump": "Unit 1",
        "side": "A",
        "fuel_type_id": 1,
        "fuel_type": "Petrol",
        "liters_sold": "40.000",
        "total_amount": "10900.00",
        "profit": "700.00"
      }
    ],
    "today_product_sales_amount": "2200.00",
    "today_credit_sales_amount": "5450.00",
    "today_expenses": "500.00",
    "today_purchase_cost": "0.00",
    "profit_today": "1500.00",
    "fuel_profit_today": "1750.00",
    "product_profit_today": "250.00",
    "credit_outstanding": "5450.00",
    "open_shift": null,
    "tank_levels": [
      {
        "id": 1,
        "name": "Petrol Tank",
        "fuel_type": "Petrol",
        "capacity": "20000.000",
        "current_stock": "11900.000",
        "fill_percentage": "59.50",
        "low_level_threshold": "2000.000",
        "is_low": false
      }
    ],
    "alerts": []
  }
}
```

## GET /reports/daily

```json
{
  "success": true,
  "message": "OK",
  "data": {
    "date": "2026-09-05",
    "fuel": {
      "sales_amount": "27250.00",
      "liters_sold": "100.000",
      "cogs": "25500.00",
      "profit": "1750.00",
      "purchase_cost": "0.00",
      "profit_vs_purchases": "27250.00",
      "by_fuel_type": [
        {
          "fuel_type_id": 1,
          "fuel_type": "Petrol",
          "code": "petrol",
          "liters_sold": "80.000",
          "total_amount": "21800.00",
          "profit": "1400.00"
        }
      ],
      "by_nozzle": [
        {
          "nozzle_id": 1,
          "label": "Unit 1 Side A",
          "pump": "Unit 1",
          "side": "A",
          "fuel_type_id": 1,
          "fuel_type": "Petrol",
          "liters_sold": "40.000",
          "total_amount": "10900.00",
          "profit": "700.00"
        }
      ]
    },
    "products": {
      "sales_amount": "0.00",
      "cost": "0.00",
      "profit": "0.00"
    },
    "credit_sales_amount": "5450.00",
    "expected_cash": "21800.00",
    "expenses": "0.00",
    "net_profit": "1750.00",
    "shifts": []
  }
}
```

## Error: closed shift is immutable

```json
{
  "success": false,
  "message": "Records cannot be changed after the shift is closed."
}
```
