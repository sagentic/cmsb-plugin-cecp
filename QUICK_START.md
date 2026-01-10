# Quick Start Guide

## Installation (2 minutes)

1. Upload `conditionalErrorCheckingPro/` folder to `/cmsb/plugins/`
2. Run `fixown` command
3. Access CMS admin - tables auto-create

## Create Your First Rule (1 minute)

1. Go to **Plugins > Conditional Error Checking Pro**
2. Click **Rules** tab
3. Click **Add New Rule**
4. Fill in:
   - **Table:** Select your table
   - **Rule Name:** "Example Rule"
   - **Trigger Field:** Select field that triggers the rule
   - **Condition:** "is not empty"
   - **Required Field:** Select field that becomes required
   - **Error Message:** "This field is required when..."
5. Click **Save Rule**

## Test It

1. Go edit a record in your selected table
2. Enter a value in the trigger field
3. Leave the required field empty
4. Click Save
5. You should see your error message

## That's It!

Your validation rule is now active. Visit the Help page for more condition types and examples.
