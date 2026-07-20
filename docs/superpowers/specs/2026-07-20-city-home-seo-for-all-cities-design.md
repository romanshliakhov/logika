# City home SEO section for all cities

## Scope

Populate the existing homepage city SEO section for every published `city` record. The section remains editable in the existing ACF tab `SEO-секція головної`.

## Data and behavior

- Reuse the existing `city_home_seo_*` fields and `/logika/v1/cities/{id}/homepage-seo` endpoint.
- Seed only missing fields, preserving every existing editor value.
- Generate Ukrainian title, two-paragraph description and video caption from each city title; use the existing CTA and approved shared media/video as fallbacks.
- The section stays hidden for an incomplete city, as it does now.

## Verification

- A focused PHP test proves a published city gets a complete response while pre-filled city content is unchanged.
- Run the existing homepage-city-SEO test and a browser smoke check after the seed.
